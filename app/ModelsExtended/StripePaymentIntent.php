<?php

namespace App\ModelsExtended;

use App\Repositories\Stripe\StripeConnectSDK;
use Illuminate\Support\Arr;
use Stripe\Exception\ApiErrorException;

class StripePaymentIntent extends \App\Models\StripePaymentIntent
{
    /**
     * @return string|null
     */
    public function getStripeIntentIdAttribute(): string
    {
        return  array_to_object($this->stripe_response)->id;
    }

    /**
     * @return string|null
     */
    public function getStripeChargeIdAttribute(): ?string
    {
        return $this->succeeded ? Arr::first(array_to_object($this->stripe_response)->charges->data)->id  : null;
    }

    public function getStripeTransferGroupAttribute()
    {
        return $this->succeeded ? $this->stripe_response["transfer_group"]  : null;
    }

    /**
     * @return $this
     * @throws ApiErrorException
     */
    public function retrieveUpdatedData(): StripePaymentIntent
    {
        $SDK = new StripeConnectSDK();

        $intent = $SDK->retrievePaymentIntent( $this->getStripeIntentIdAttribute() );

        $this->update([
            "stripe_response" => $intent->toArray(),
            "succeeded" => ($intent->status == "succeeded"),
        ]);

        return $this;
    }

}
