<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Copilot\ChatRequestsViewListTrait;
use App\ModelsExtended\AccountStatus;
use App\ModelsExtended\AdvisorRequest;
use App\ModelsExtended\Role;
use App\ModelsExtended\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use App\ModelsExtended\AdvisorRequestStatus;

class AdvisorRequestsController extends Controller
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

    public function quickList()
    {
        return $this->showList();
    }

    /**
     * @return Builder
     */
    private function getDataQuery()
    {

        if($this->user->agency_usage_mode_id == 1): //If Copilot then skip archived Request
            return AdvisorRequest::query()->where("created_by_id", $this->user->id)->whereNotIn('id', function ($query) {
                $query->select('advisor_request_id')->from('advisor_request_archived')->where('copilot_id',$this->user->id);
            });
        else:
            return AdvisorRequest::query()->where("created_by_id", $this->user->id);
        endif;

    }

    /**
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function concierges()
    {
        $concierges =  User::query()
            ->where("account_status_id", AccountStatus::APPROVED)
            ->whereHas("roles", function (Builder $builder) {
                $builder->where("user_role.role_id", Role::Concierge);
            })
            ->paginate(5);
            $concierges->getCollection()->transform(function ($user) {
                return array_merge(Arr::only($user->presentForDev(), [
                    "id",
                    "profile_image_url",
                    "first_name",
                    "country",
                    "city",
                    "preferred_timezone_tzab",
                    "co_pilot_duties",
                ]),[
                    "copilot_info" => $user->copilot_info,
                    "feedback" => $user->rating, //call rating object
                    "country_alpha2_code" => $user->country->iso_3166_1_alpha2_code,
                ]);
            });
            return $concierges;

    }

    /**
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     * @throws ValidationException
     */
    public function topConcierges(Request $request)
    {
        $this->validatedRules([
            "limit" => "integer|min:1|max:1000"
        ]);

        return User::query()
            ->where("account_status_id", AccountStatus::APPROVED)
            ->withCount("average_feedback" )
            ->withSum("average_feedback", "average_rating")
            ->whereHas("roles", function (Builder $builder) {
                $builder->where("user_role.role_id", Role::Concierge);
            })
            ->orderByDesc("average_feedback_sum_average_rating")
            ->limit( $request->input("limit", 2) )
            ->get()
            ->map(fn(User $user) => [
                "id" => $user->id,
                "first_name" => $user->first_name,
                "bio" => ($user->copilot_info)?$user->copilot_info->bio:'',
                "country" => ($user->country)?$user->country->description:'',
                "country_alpha2_code" => $user->country->iso_3166_1_alpha2_code,
                "city" => $user->city,
                "preferred_timezone_tzab" => $user->preferred_timezone_tzab,
                "profile_image_url" => $user->profile_image_url,
                "average_feedback_sum_average_rating" => $user->rating,
                "average_feedback_count" => $user->average_feedback_count,
            ]);
    }
       /**
     *
     * the Copilot image + name should appear there should be a max of 10 items in this list. It should always show the 10 most recent Requests
     * Completed by a CoPilot + the rating they (the advisor) gave the
     *  copilot on that specific task“Your Review” should show the rating
     * they gave the copilot tied to 1 request - this is not the CoPilots
     * average rating, it is the specific rating they received from this
     * advisor on the last tasks completed
     */

    public function recentConcierges(Request $request)
    {
         return \DB::select("SELECT arf.*,users.*
         FROM users
             JOIN advisor_assigned_copilot AS aac ON aac.copilot_id = users.id
             JOIN advisor_request_feedback AS arf ON arf.advisor_request_id = aac.advisor_request_id
             where aac.advisor_request_id = (
                SELECT  advisor_request_id
                FROM advisor_assigned_copilot aacc
                LEFT JOIN advisor_request AS ar ON ar.id = aacc.advisor_request_id
                where copilot_id = users.id
                AND ar.created_by_id ={$this->user->id}
                AND ar.advisor_request_status_id=4
                ORDER BY aacc.created_at DESC LIMIT 1
             )
             order by arf.created_at DESC limit 10
             /* GROUP BY users.id AND ar.id IS  NULL*/
            ");
    }

    /**
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     * @throws ValidationException
     */
    public function recentRequests(Request $request)
    {
        $this->validatedRules([
            "limit" => "integer|min:1|max:1000"
        ]);

        return $this->getDataQuery()
            ->with( "advisor_request_status","advisor_assigned_copilot", "advisor_request_attachments","user" )
            ->where("advisor_request.advisor_request_status_id","!=", AdvisorRequestStatus::DRAFT )
            ->latest("created_at")
            ->withCount(
                ['advisor_chats' => function ($query)
                    {
                         $query->where('seen', false);
                    }
            ])
            ->limit( $request->input("limit", 10) )
            ->orderBy('id','desc')
            ->get()
            ->map( function(AdvisorRequest $advisorRequest) {
                return
                    array_merge(
                        $advisorRequest->presentForDev(),
                        [
                            "created_at" => $advisorRequest->created_at->toIso8601String(),
                            "updated_at" => $advisorRequest->updated_at->toIso8601String(),
                            // 'has_unread_messages' => $advisorRequest->advisor_chats_count > 0,
                            // 'unread_messages' => $advisorRequest->advisor_chats_count,
                        ]
                    );
            });
    }
}

