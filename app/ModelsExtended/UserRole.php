<?php

namespace App\ModelsExtended;

use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;
use Illuminate\Support\Carbon;

/**
 * @property User $user
 */
class UserRole extends \App\Models\UserRole implements IDeveloperPresentationInterface
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return string|null
     */
    public function getStripeSubscriptionIdAttribute(): ?string
    {
        return $this->stripe_subscription ? $this->stripe_subscription['id'] : null;
    }

    /**
     * @return string|null
     */
    public function getStripeSubscription(): ?array
    {
        return $this->stripe_subscription ? json_encode($this->stripe_subscription,true):[];
    }


    /**
     * @return string|null
     */
    public function getSubscriptionPlanNickName(): ?string
    {
        return $this->stripe_subscription ? $this->stripe_subscription['plan']['nickname'] : null;
    }

    /**
     * @return Carbon|null
     */
    public function getStripeSubscriptionEndDateAttribute(): ?Carbon
    {
        return $this->stripe_subscription ? Carbon::createFromTimestamp( $this->stripe_subscription['current_period_end'] ) : null;
    }

    /**
     * @return string|null
     */
    public function getStripeLatestInvoiceIdAttribute(): ?string
    {
        return $this->stripe_subscription ? $this->stripe_subscription['latest_invoice'] : null;
    }

    /**
     * @return bool
     */
    public function hasActiveStripeSubscriptionAttribute(): bool
    {
        return $this->stripe_subscription && $this->stripe_subscription['status'] === 'active';
    }

    /**
     * @param int $role_id
     * @param int $user_id
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null|UserRole
     */
    public static function getUserRole(int $role_id,int $user_id )
    {
        return self::query()
            ->where("role_id", $role_id)
            ->where("user_id", $user_id)
            ->first();
    }

    public function isCopilot(): bool
    {
        return $this->role_id === Role::Concierge;
    }

    public function presentForDev(): array
    {
        return [
            "id" => $this->id,
            "role_id" => $this->role_id,
            "role" => $this->role->description,
        ];
    }
}
