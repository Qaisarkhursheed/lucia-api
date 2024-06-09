<?php

namespace App\Http\Controllers\Copilot;

use App\ModelsExtended\AdvisorChat;
use App\ModelsExtended\AdvisorRequest;
use App\ModelsExtended\AdvisorRequestStatus;
use App\ModelsExtended\AdvisorRequestActivity;
use App\ModelsExtended\Role;
use App\ModelsExtended\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Use this to detect if user has unread messages
 *
 * @property User $user
 *
 * @method Builder getDataQuery
 */
trait ChatRequestsViewListTrait
{

    /**
     * @return array
     */
    public function notifications(Request $request): array
    {
        $user_id = isset($request->user_id) ? $request->user_id : $this->user->id;
        $isCopilot = $this->user->hasRole( Role::Concierge );
        $count = AdvisorChat::with("advisor_request")
            ->where("seen", false)
            ->where("notified", true)
            ->where("receiver_id", $user_id)
            ->whereHas("advisor_request", function (Builder $builder){
                $builder->where("advisor_request.advisor_request_status_id", AdvisorRequestStatus::ACCEPTED );
            })
            ->count();

        $notifications  = AdvisorRequestActivity::with('advisor_request')
        ->whereHas("advisor_request", function (Builder $builder) use($isCopilot, $user_id) {
            // $builder->where("advisor_request.advisor_request_status_id","!=", AdvisorRequestStatus::ACCEPTED );
            // $builder->where("advisor_request.advisor_request_status_id","!=", AdvisorRequestStatus::COMPLETED);

            if($isCopilot){
                $builder->whereHas("advisor_assigned_copilot", function ($copilot) use($user_id) {
                    $copilot->where('advisor_assigned_copilot.copilot_id', $user_id);
                });
            }
            else{
                $builder->where("advisor_request.created_by_id", $user_id );
            }
        })
        ->where('receiver_id', $user_id)
        ->orderBy('created_at',"desc");

        return [
            "notificationDetails" => $notifications->orderBy('created_at',"desc")->get(),
            "notifications" => $count+($notifications->where('is_seen',false)->count()),
            "notificationCount" => $notifications->where('is_seen',false)->count(),

        ];
    }
    public function markAsRead()
    {
        $isCopilot = $this->user->hasRole( Role::Concierge );
        $notifications  = AdvisorRequestActivity::with('advisor_request')
        ->whereHas("advisor_request", function (Builder $builder) use($isCopilot) {

            $builder->whereOr("advisor_request.advisor_request_status_id", AdvisorRequestStatus::ACCEPTED );
            $builder->whereOr("advisor_request.advisor_request_status_id", AdvisorRequestStatus::PAID );
            $builder->whereOr("advisor_request.advisor_request_status_id", AdvisorRequestStatus::COMPLETED );
            if($isCopilot)
            {
                $builder->whereHas("advisor_assigned_copilot", function ($copilot) {
                    $copilot->where('advisor_assigned_copilot.copilot_id', $this->user->id);
                });
            }
            else{
                $builder->where("advisor_request.created_by_id", $this->user->id );
            }
        })->update(['advisor_request_activity.is_seen'=>true]);

    }
        /**
     * @return array
     */
    public function unReadMessages()
    {
        // $data = \DB::table('advisor_chat')->select('advisor_chat.*')
        //     // ->where("max_user.seen", false)
        //     // ->where("max_user.receiver_id", $this->user->id)
        //     // ->whereHas("advisor_request", function (Builder $builder){
        //     //     $builder->where("advisor_request.advisor_request_status_id", AdvisorRequestStatus::ACCEPTED );
        //     // })
        //     // ->groupBy('latest.advisor_request_id')
        //     ->where("advisor_chat.id","=",\DB::raw("(select max(id) as max_id from advisor_chat group by advisor_request_id )"))
        //     ->where("advisor_chat.receiver_id","=",$this->user->id)
        //     // ->groupBy('advisor_chat.advisor_request_id')
        //     ->orderBy('advisor_chat.id',"desc")
        //     ->get();

        $data =  \DB::table('advisor_chat AS m1')
                    ->leftjoin('advisor_chat AS m2', function($join) {
                        $join->on('m1.advisor_request_id', '=', 'm2.advisor_request_id');
                        $join->on('m1.id', '<', 'm2.id');
                        })
                        ->join('advisor_request','advisor_request.id','=','m1.advisor_request_id')
                        ->leftjoin('advisor_assigned_copilot','advisor_assigned_copilot.advisor_request_id','=','m1.advisor_request_id')
                        ->whereNull('m2.id')
                        ->select('advisor_request.*','m1.*')
                        ->where("m1.receiver_id", $this->user->id)
                        ->where("m1.seen", false)
                        ->orderBy('m1.id', 'desc')->get();
                return $data;
    }
    /**
     * @return array Exception|\Illuminate\Support\Collection
     */
    public function showList()
    {
        return $this->getDataQuery()
            ->with( "advisor_request_status", "advisor_request_attachments")
            // append chat count
            ->withCount([ "advisor_chats" => function( Builder $builder ){
                return $builder->where( 'advisor_chat.receiver_id' , $this->user->id )
                    ->where( 'advisor_chat.seen' , false );
            }])
            // ->orderByRaw( "if(advisor_request_status_id=3, id, 100000+id )" )
            ->orderByRaw( "FIELD(advisor_request_status_id, 3, 5, 4,5)" )

            ->get()
            ->map( function(AdvisorRequest $request) {
                return
                    array_merge(
                        $request->presentForDev(),
                        [
                            'has_unread_messages' => $request->advisor_chats_count > 0,
                            'unread_messages' => $request->advisor_chats_count,
                        ]
                    );
            });
    }
}

