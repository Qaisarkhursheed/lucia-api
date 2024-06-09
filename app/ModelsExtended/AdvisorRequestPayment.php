<?php

namespace App\ModelsExtended;

/**
 * @property StripePaymentIntent|null $stripe_payment_intent
 * @property AdvisorRequest $advisor_request
 */
class AdvisorRequestPayment extends \App\Models\AdvisorRequestPayment
{
    public function stripe_payment_intent()
    {
        return $this->belongsTo(StripePaymentIntent::class);
    }

    public function advisor_request()
    {
        return $this->belongsTo(AdvisorRequest::class);
    }


}
