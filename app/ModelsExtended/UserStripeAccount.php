<?php

namespace App\ModelsExtended;

use Illuminate\Support\Carbon;

class UserStripeAccount extends \App\Models\UserStripeAccount
{
    /**
     * @return string|null
     */
    public function getStripeCustomerIdAttribute(): ?string
    {
        return $this->stripe_customer ? $this->stripe_customer['id'] : null;
    }

    /**
     * @return string|null
     */
    public function getStripeDefaultSourceAttribute(): ?string
    {
        // TODO: Write a matching code to fix stripe returning source
        //  that doesn't exists as payment method
        return $this->stripe_customer ? $this->stripe_customer['default_source'] : null;
    }

    /**
     * like acct_1K6FWsQeejgmvWPe
     *
     * @return string|null
     */
    public function getStripeConnectIdAttribute(): ?string
    {
        return $this->stripe_connect_account ? $this->stripe_connect_account['id'] : null;
    }

    /**
     * Status message regarding the stripe connect of the user
     * @return string
     */
    public function getStripeConnectAccountStatus(): string
    {
        if( !$this->getStripeConnectIdAttribute() ) return "not created";
        return $this->connect_boarding_completed ? "fully connected" : "pending verification";
    }
}
