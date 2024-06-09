<?php

namespace App\ModelsExtended;

use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;
use App\Repositories\Stripe\StripeConnectSDK;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Stripe\Exception\ApiErrorException;
use App\Models\AdvisorRequestArchived;
use App\ModelsExtended\AdvisorRequestStatus;
use App\Models\AdvisorTaskCategory;
use App\Models\ActivityType;
use DateTime;

/**
 * @property User $user
 * @property int $due_minutes_left
 * @property int $total_minutes_assigned
 * @property float $total_amount
 * @property float $percentage_time_left
 * @property int $task_completed_count
 * @property Carbon|null $deadline_locale
 * @property Itinerary $itinerary
 * @property Collection|AdvisorRequestActivity[] $advisor_request_activities
 * @property AdvisorRequestPayment $advisor_request_payment
 * @property Collection|AdvisorRequestTask[] $advisor_request_tasks
 * @property AdvisorRequestFeedback $advisor_request_feedback
 * @property Collection|AdvisorChat[] $advisor_chats
 * @property `AdvisorAssignedCopilot` $advisor_assigned_copilot
 */
class AdvisorRequest extends \App\Models\AdvisorRequest implements IDeveloperPresentationInterface
{
    protected $appends = [ "due_minutes_left", "total_minutes_assigned" , "percentage_time_left", 'deadline_locale', 'task_completed_count' ];

    /**
     * @param int $user_id
     * @param string $discount_code
     * @return int
     */
    public static function getDiscountUsageCount(int $user_id, string $discount_code): int
    {
        return self::query()
            ->where("created_by_id", $user_id)
            ->where("discount_code", $discount_code)
            ->where("advisor_request_status_id", '!=' , AdvisorRequestStatus::PAID)
            ->count();
    }

    /**
     * @return int
     */
    public function getDueMinutesLeftAttribute(): int
    {
        if( ! $this->deadline ) return 0;
        if( fromAppTimezoneToUserPreferredTimezone(Carbon::now(), $this->user)->greaterThan( $this->deadline ) ) return 0;
        return $this->deadline->diffInMinutes(   fromAppTimezoneToUserPreferredTimezone(Carbon::now(), $this->user) );
    }

    // Implement time zone reversal
    public function getDeadlineLocaleAttribute()
    {
        return $this->deadline? $this->deadline->fromAppTimezoneToUserPreferredTimezone($this->user) : null;
    }

    /**
     * @return int
     */
    public function getTotalMinutesAssignedAttribute(): int
    {
        if( ! $this->deadline ) return 0;
        return $this->deadline->diffInMinutes(   fromAppTimezoneToUserPreferredTimezone($this->created_at, $this->user) );
    }

    /**
     * @return float
     */
    public function getPercentageTimeLeftAttribute(): float
    {
        if( ! $this->total_minutes_assigned || !$this->due_minutes_left || $this->due_minutes_left > $this->total_minutes_assigned ) return 0;
        return round( ($this->due_minutes_left / $this->total_minutes_assigned ) * 100, 1 );
    }

    /**
     * @return int
     */
    public function getTaskCompletedCountAttribute(): int
    {
        return $this->advisor_request_tasks->where("completed", true)->count();
    }

    /**
     * @return $this
     * @throws ApiErrorException
     */
    public function refundCharge(): AdvisorRequest
    {
        $SDK = new StripeConnectSDK();

        if(isset($this->advisor_request_payment->stripe_charge_id))
        {
            $refund = $SDK->createRefund( $this->advisor_request_payment->stripe_charge_id );

            $this->update([
                'stripe_refund_id' => $refund->id,
                "advisor_request_status_id" => AdvisorRequestStatus::REFUNDED,
            ]);
            $this->createActivity((isset($this->advisor_assigned_copilot->user->first_name) ? $this->advisor_assigned_copilot->user->first_name : ''). " refunded request ".$this->request_title,ActivityType::ADVISOR_REQUEST, $this->created_by_id);


        }
        else
        {
            \Log::info('Strip charge id not found to Refunded request '.$this->id);
            $this->update([
                // 'stripe_refund_id' => $refund->id,
                "advisor_request_status_id" => AdvisorRequestStatus::REFUNDED,
            ]);
            $this->createActivity((isset($this->advisor_assigned_copilot->user->first_name) ? $this->advisor_assigned_copilot->user->first_name : ''). " refunded request ".$this->request_title,ActivityType::ADVISOR_REQUEST, $this->created_by_id);
        }

        return $this;
    }

    /**
     * @param int $id
     * @return AdvisorRequest
     */
    public static function getById( int $id )
    {
        return self::find($id);
    }

    /**
     * @param string $details
     * @param integer $type
     * @return \Illuminate\Database\Eloquent\Model|AdvisorRequestActivity
     */
    public function createActivity( string $details, $type= ActivityType::ADVISOR_REQUEST, $receiver_id = null) //2 meaning advisor request
    {
        return $this->advisor_request_activities()->create([
            "details" => $details,
            "type" => $type,
            "receiver_id" => $receiver_id,

        ]);
    }

    /**
     * @return $this
     */
    public function recalculateTotalAmount(): AdvisorRequest
    {
        $SDK = new StripeConnectSDK();
        $this->sub_amount = $this->advisor_request_tasks->sum( fn(AdvisorRequestTask $task) => ($task->amount > 0)?$task->amount:$task->hourly_rate );

        if($this->discount >= $this->sub_amount):
            $this->total_amount = 0;
            $this->fee_amount = 0;
            $this->sub_amount = 0;
        else:
            $this->total_amount = $SDK->addStripeFeeExclusive($this->sub_amount - $this->discount );
            $this->fee_amount = $this->total_amount - ($this->sub_amount - $this->discount);
        endif;

        $this->updateQuietly();

        return $this;
    }

    public function advisor_assigned_copilot()
    {
        return $this->hasOne(AdvisorAssignedCopilot::class);
    }

    public function advisor_request_feedback()
    {
        return $this->hasOne(AdvisorRequestFeedback::class);
    }

    public function advisor_request_tasks()
    {
        return $this->hasMany(AdvisorRequestTask::class);
    }

    public function advisor_request_payment()
    {
        return $this->hasOne(AdvisorRequestPayment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by_id')->withTrashed();
    }

    public function advisor_request_attachments()
    {
        return $this->hasMany(AdvisorRequestAttachment::class);
    }

    public function advisor_request_activities()
    {
        return $this->hasMany(AdvisorRequestActivity::class);
    }

    public function itinerary()
    {
        return $this->belongsTo(Itinerary::class);
    }

    public function advisor_chats()
    {
        return $this->hasMany(AdvisorChat::class);
    }

    public function getFolderStorageRelativePath(): string
    {
        return sprintf(
            "%s/advisor-requests/%d",
            $this->user->getFolderStorageRelativePath(),
            $this->id
        );
    }

    /**
     * @inheritDoc
     */
    public function presentForDev(): array
    {
        $first_name = null;
        $last_name = null;
        $copilotInfo = null;
        $profile_image_url = null;
        if($this->advisor_assigned_copilot){
            $copilotInfo = $this->advisor_assigned_copilot->user?$this->advisor_assigned_copilot->user->copilot_info : null;
            $first_name = $this->advisor_assigned_copilot->user?$this->advisor_assigned_copilot->user->first_name : null;
            $last_name = $this->advisor_assigned_copilot->user?$this->advisor_assigned_copilot->user->last_name : null;
            $profile_image_url = $this->advisor_assigned_copilot->user?$this->advisor_assigned_copilot->user->profile_image_url : null;

        }

        $result = [
            'id' => $this->id,
            'request_type' => $this->request_type,
            'deadline' => optional($this->deadline)->toIso8601String(),
            'deadline_locale' => optional($this->deadline_locale)->toIso8601String(),
            'itinerary_id' => $this->itinerary_id,
            'itinerary_title' => optional($this->itinerary)->title,
            'itinerary_api_preview_url' => optional($this->itinerary)->getApiPreviewUrl(),
            'itinerary_app_preview_url' => optional($this->itinerary)->getAppPreviewURL(),
            'discount_code' => $this->discount_code,
            'discount' => $this->discount,
            'sub_amount' => $this->sub_amount,
            'completed_at' => $this->completed_at,
            'fee_amount' => $this->fee_amount,
            'total_amount' => $this->total_amount,
            'request_title' => $this->request_title,
            'owner' => $this->user->name,
            'first_name' => $this->user->first_name,
            'last_name' => $this->user->last_name,
            'owner_profile_image_url' => $this->user->profile_image_url,
            'created_at' => $this->created_at->toIso8601String(),
            'advisor_request_status_id' => $this->advisor_request_status_id,
            'advisor_request_status' => isset($this->advisor_request_status->description) ? $this->advisor_request_status->description : null,
            'notes' => $this->notes,
            'total_minutes_assigned' => $this->total_minutes_assigned,
            'due_minutes_left' => $this->due_minutes_left,
            'percentage_time_left' => $this->percentage_time_left,
            'task_completed_count' => $this->task_completed_count,
            'categories'=>null,
            'copilot_last_name' => $last_name,
            'copilot_first_name' => $first_name,
            'copilot_avatar' => $profile_image_url,
            'copilot_bio'=>$copilotInfo,
            'tasks' => $this->advisor_request_tasks->map->presentForDev(),
            'activities' => $this->advisor_request_activities->reverse()->take(5)->reverse()->values()->map->presentForDev(),
            'advisor_request_attachments' => $this->advisor_request_attachments->map->presentForDev(),
            'assigned_copilot' => isset($this->advisor_assigned_copilot->user) ? $this->advisor_assigned_copilot->user : null,
            'has_unread_messages' => ($this->advisor_chats->where('receiver_id',$this->created_by_id)->where('seen', false)->count() > 0) ? true : false,
            'messages' => $this->advisor_chats->take(1)->sortByDesc('created_at')->map->presentForDev(),
            "feedback" => isset($this->advisor_assigned_copilot->user) ? $this->advisor_assigned_copilot->user->rating: null, //call rating object
            "country" => isset($this->advisor_assigned_copilot->country->description ) ? $this->advisor_assigned_copilot->country->description: null, //call rating object
        ];


        // $categories = '';
        // foreach($result['tasks'] as $task){
        //     if($task['categories']){
        //         $categories .= !empty($categories)?",".$task['categories']:$task['categories'];
        //     }
        // }

        // if($categories){
        //     $CategoriesArray = explode(',',$categories);
        //     $result['categories'] = implode(',',array_unique($CategoriesArray));
        // }


       return $result;
    }


    /**
     * @param int $task_id
     * @return AdvisorRequestTask|null
     */
    public function getTask(int $task_id): ?AdvisorRequestTask
    {
        return $this->advisor_request_tasks->where("id", $task_id)->first();
    }
    public function archivedRequests()
    {
        return $this->hasMany(AdvisorRequestArchived::class, 'advisor_request_id', 'id');
    }

    public function getDaysPosted($date){

        $date2 = date('Y-m-d');
        $date1 = date('Y-m-d',strtotime($date));
         // Calculating the difference in timestamps
        $diff = strtotime($date2) - strtotime($date1);

      // 1 day = 24 hours
      // 24 * 60 * 60 = 86400 seconds
      return abs(round($diff / 86400));
    }
}
