<?php

namespace App\Http\Controllers\Copilot;

use App\Http\Controllers\Controller;
use App\Mail\Agent\AdvisorRequestAcceptedMail;
use App\ModelsExtended\AdvisorRequest;
use App\ModelsExtended\AdvisorRequestStatus;
use App\ModelsExtended\User;
use App\Models\ActivityType;
use App\Repositories\Pusher\PushNotifications\AdvisorRequestAcceptedPushNotification;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class OpenRequestsController extends Controller
{
    use ChatRequestsViewListTrait;

    /**
     * @var Authenticatable|User
     */
    private $user;

    public function __construct()
    {
        $this->user = auth()->user();
    }

    public function fetchAll()
    {
        return $this->showList();
    }

    /**
     * @return Builder
     */
    private function getDataQuery(): Builder
    {
        return AdvisorRequest::query()
            ->where("advisor_request_status_id", AdvisorRequestStatus::PAID)

            // only show the ones that are not assigned or that are assigned to me
            // ->where(function (Builder $builder){
            //     $builder->whereDoesntHave("advisor_assigned_copilot")
            //     ->orWhereHas("advisor_assigned_copilot", function (Builder $builder){
            //         $builder->where("advisor_assigned_copilot.copilot_id", $this->user->id);
            //     });
            // })

           /*
                exclude the requests that are archived by a particular Copilot (user) and also the request that are just assigned to a Copilot
           */
            ->where(function (Builder $builder){
                $builder->whereDoesntHave("advisor_assigned_copilot")
                ->whereDoesntHave("archivedRequests", function (Builder $builder){
                    $builder->where("advisor_request_archived.copilot_id", $this->user->id);
                });
            })


            ->orderByDesc("created_at");
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws ValidationException
     * @throws \Exception
     */
    public function accept(Request $request)
    {
        $this->validatedRules([
            'advisor_id' => 'required|numeric|exists:advisor_request,id',
        ]);

        $advisor_request_id = $request->input( 'advisor_id' );

        // make this atomic
        // ----------------------------------------------------
        // return $this->runInALock('accepting-advisor-' . $advisor_request_id, function () use ($request, $advisor_request_id) {

                // $advisor_request = $this->getRequestOpened($advisor_request_id);
                $advisor_request = AdvisorRequest::find($advisor_request_id);

                if ($advisor_request->advisor_request_status_id !== AdvisorRequestStatus::PAID)
                    throw new \Exception('You can not accept this advisor request because it is not in paid status anymore.');

                try {

                    DB::transaction(function ( ) use ( $advisor_request ){

                        if( !$advisor_request->advisor_assigned_copilot )
                            $advisor_request->advisor_assigned_copilot()->create([
                                'copilot_id' => $this->user->id,
                            ]);

                        $advisor_request->advisor_request_status_id = AdvisorRequestStatus::ACCEPTED;
                        $advisor_request->update();

                        $advisor_request->createActivity("Accepted Request",ActivityType::ADVISOR_REQUEST,  $advisor_request->created_by_id);
                         // inform user
                         Mail::send(new AdvisorRequestAcceptedMail($advisor_request, $this->user));
                         dispatch( new AdvisorRequestAcceptedPushNotification( $advisor_request ) );

                    });

                    return $advisor_request->refresh()->presentForDev();

                } catch (ValidationException $exception) { throw $exception; }
                catch (\Exception $exception) {
                    DB::rollback();
                    Log::error($exception->getMessage(), $exception->getTrace());
                    throw new \Exception("Sorry, this request can't be accepted! Please, try again later", 0, $exception);
                }
            // });
    }

    /**
     * @param $advisor_request_id
     * @return Builder|\Illuminate\Database\Eloquent\Model|AdvisorRequest
     */
    private function getRequestOpened($advisor_request_id)
    {
        return $this->getDataQuery()
            ->where("id", $advisor_request_id)
            ->firstOrFail();
    }
}

