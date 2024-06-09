<?php

namespace App\Http\Controllers\Admin\Copilots;

use App\Http\Controllers\Enhancers\CRUDEnabledController;
use App\Http\Controllers\Enhancers\YajraPaginableTraitController;
use App\Http\Responses\OkResponse;
use App\ModelsExtended\AdvisorRequest;
use App\ModelsExtended\AdvisorRequestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;




/**
 * @property AdvisorRequest $model
 */
class AdvisorRequestsController  extends CRUDEnabledController implements \App\Http\Controllers\Enhancers\IYajraEloquentResultProcessorInterface
{
    use YajraPaginableTraitController;

    public function __construct()
    {
        parent::__construct( 'advisor_request_id' );
    }

    /**
     * @return array|Builder[]|\Illuminate\Database\Eloquent\Collection|JsonResponse
     * @throws ValidationException
     */
    public function fetchAll()
    {
        return $this->paginateYajra(  $this );
    }

    /**
     * @return Builder
     */
    protected function getQuery(): Builder
    {
        return AdvisorRequest::query()
            ->whereNotIn("advisor_request_status_id", [ AdvisorRequestStatus::DRAFT ]);
    }

    /**
     * @param Builder $query
     * @return Builder|mixed
     */
    protected function filterQuery(Builder $query)
    {
        return $query->when($this->search, function (Builder $builder) {
            $search = $this->search;
            $builder->where( function (Builder $builder) use ( $search ) {
                $builder->where("request_title", 'like', "%$search%")
                    ->orWhere("notes", 'like', "%$search%");
            });
        });
    }

    /**
     * @inheritDoc
     */
    public function getDataQuery(): Builder
    {
        return $this->getQuery();
    }

    /**
     * @inheritDoc
     */
    public function store(Request $request)
    {
    }


    public function list(Request $request)
    {
        $result = AdvisorRequest::query()->whereIn('id', function ($query) use ($request) {
                $query->select('advisor_request_id')->from('advisor_request_archived')->where('copilot_id',$request->copilot_id);
            })->orderByDesc("request_title")
                ->get()
                ->map(fn(AdvisorRequest $AdvisorRequest) => [
                    $AdvisorRequest->request_title,
                    $AdvisorRequest->user->name,
                    $AdvisorRequest->total_amount,
                    $AdvisorRequest->sub_amount,
                    $AdvisorRequest->fee_amount,
                    $AdvisorRequest->advisor_request_status->description,
                    date('m/d/Y',strtotime($AdvisorRequest->created_at))
                ]);

        return datatables($result)->rawColumns(['action'])->make(true);
    }

    /**
     * This will remove assigned copilot if any
     *
     * @throws \Exception
     */
    public function makeAvailable(Request $request): OkResponse
    {
        if(
            $this->model->advisor_request_status_id !== AdvisorRequestStatus::ACCEPTED
            && $this->model->advisor_request_status_id !== AdvisorRequestStatus::PAID
        ) throw new \Exception("You can not perform this action on this request!");

        $this->model->update(["advisor_request_status_id" =>AdvisorRequestStatus::PAID]);
        $this->model->advisor_assigned_copilot()->delete();

        return new OkResponse($this->model->presentForDev());
    }
    public function refundRequest(Request $request): OkResponse
    {
        if(
            $this->model->advisor_request_status_id !== AdvisorRequestStatus::ACCEPTED
            && $this->model->advisor_request_status_id !== AdvisorRequestStatus::PAID
        ) throw new \Exception("You can not perform this action on this request!");
        $this->model->refundCharge();
        // $this->model->update(["advisor_request_status_id" =>AdvisorRequestStatus::REFUNDED]);
        // $this->model->advisor_assigned_copilot()->delete();

        return new OkResponse($this->model->presentForDev());
    }



    /**
     * @param AdvisorRequest[]|Collection $result
     * @return array
     */
    public function processYajraEloquentResult($result): array
    {
        return $result->map(
            function (AdvisorRequest $advisorRequest){
                return array_merge( Arr::only($advisorRequest->presentForDev(), [
                                  'id',
                                'deadline',
                                'deadline_locale',
                                'itinerary_title',
                                'itinerary_app_preview_url',
                                'sub_amount',
                                'fee_amount',
                                'total_amount',
                                'request_title',
                                'owner',
                                'created_at',
                                'advisor_request_status_id',
                                'advisor_request_status',
                                'notes',
                                'total_minutes_assigned',
                                'due_minutes_left',
                                'percentage_time_left',
                                'task_completed_count',
                                'copilot_last_name',
                                'copilot_first_name',"completed_at","request_type"
                    ]), [
                    "task_count" => $advisorRequest->advisor_request_tasks->count(),
                    "copilot_is_paid" => optional($advisorRequest->advisor_assigned_copilot)->is_paid,
                    'copilot_stripe_setup' => optional(optional(optional($advisorRequest->advisor_assigned_copilot)->user)->user_stripe_account)->connect_boarding_completed,
                ]);
            }
        )->toArray();
    }

    /**
        * @param ExortAdvisorRequests[]|Collection $result
        * @return array
    */
    public function export()
    {

        // Logic to fetch all requests from the advisor_request table
        $requests = AdvisorRequest::with('advisor_assigned_copilot','user')->orderBy('advisor_request.id','desc')->get();
        return $requests;
    }
}

