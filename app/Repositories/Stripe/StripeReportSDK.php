<?php

namespace App\Repositories\Stripe;

use Stripe\Invoice;
use App\ModelsExtended\StripeAuditLog;
use Stripe\Exception\ApiErrorException;

class StripeReportSDK extends StripeSubscriptionSDK
{
   
    /**
     * @return \Stripe\Balance
     * @throws ApiErrorException
     */
    public function retrieveOwnersBalance( ): \Stripe\Balance
    {
        try {

            return $this->stripe->balance->retrieve();

        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    /**
     * @return \Stripe\Invoice
     * @throws ApiErrorException
     */
    public function retrieveAllInvoice( )
    {
        try {

            return $this->stripe->invoices->all(['limit' => 10000]);

        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

}
