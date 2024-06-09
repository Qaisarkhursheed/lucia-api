<?php

namespace App\ModelsExtended;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Stripe\Subscription;

/**
 * @property User $user
 */
class StripeSubscriptionHistory extends \App\Models\StripeSubscriptionHistory
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public static function pushToHistory(UserRole $userRole)
    {
        return self::create([
            'user_id' => $userRole->user_id,
            'role_id' => $userRole->role_id,
            'stripe_subscription' => $userRole->stripe_subscription,
            'status' => $userRole->stripe_subscription['status'],
            'plan_interval' => $userRole->stripe_subscription['plan']['interval'],
            'amount_decimal' => $userRole->stripe_subscription['plan']['amount_decimal']/100,
            'current_period_start' => Carbon::createFromTimestamp( $userRole->stripe_subscription['current_period_start'] ),
            'current_period_end' => Carbon::createFromTimestamp( $userRole->stripe_subscription['current_period_end'] ),
            'start_date' => Carbon::createFromTimestamp( $userRole->stripe_subscription['start_date'] ),
            'ended_at' => $userRole->stripe_subscription['ended_at'] ? Carbon::createFromTimestamp( $userRole->stripe_subscription['ended_at'] ) : null,
        ]);
    }

    public static function pushToDirectHistory(UserRole $userRole, Subscription $subscription)
    {
        $subscriptionArray = $subscription->toArray();
        return self::create([
            'user_id' => $userRole->user_id,
            'role_id' => $userRole->role_id,
            'stripe_subscription' => $subscription->toArray(),
            'status' => $subscriptionArray['status'],
            'plan_interval' => $subscriptionArray['plan']['interval'],
            'amount_decimal' => $subscriptionArray['plan']['amount_decimal']/100,
            'current_period_start' => Carbon::createFromTimestamp( $subscriptionArray['current_period_start'] ),
            'current_period_end' => Carbon::createFromTimestamp( $subscriptionArray['current_period_end'] ),
            'start_date' => Carbon::createFromTimestamp( $subscriptionArray['start_date'] ),
            'ended_at' => $subscriptionArray['ended_at'] ? Carbon::createFromTimestamp( $subscriptionArray['ended_at'] ) : null,
        ]);
    }

    /**
     * @return array
     */
    public function formatForDisplay(): array
    {
        return [
            "role" => $this->role->description,
            "status" => $this->status,
            "plan_interval" => $this->plan_interval,
            "amount_decimal" => $this->amount_decimal,
            "current_period_start" => $this->current_period_start,
            "current_period_end" => $this->current_period_end,
            "start_date" => $this->start_date,
            "ended_at" => $this->ended_at,
        ];
    }
}
