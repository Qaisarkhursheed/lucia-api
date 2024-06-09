<?php

namespace App\ModelsExtended;

use Illuminate\Http\Request;
use Stripe\Exception\ApiErrorException;

class StripeAuditLog extends \App\Models\StripeAuditLog
{
    /**
     * @param ApiErrorException $exception
     * @param string $action_required
     * @param Request|null $request
     * @param string|null $comments
     * @return void
     */
    public static function auditRaw(ApiErrorException $exception, string $action_required = "N.A", array $inputs = [], ?string $comments = null )
    {
        StripeAuditLog::insert([
            'user_id' => auth()->id()?? User::DEFAULT_ADMIN,
            'action_required' => $action_required,
            'request_params' => json_encode( $inputs ),
            'stripe_response' => json_encode( $exception->getJsonBody()),
            'comments' => $comments,
        ]);
    }
}
