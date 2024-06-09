<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;


/**
 * Use for validating request parameters
 * Class PreConditionFailedResponse
 * @package App\Http\Responses
 */
class ServerErrorResponse extends JsonResponse
{
    public function __construct( \Exception $e,  $headers = [], $options = 0)
    {
        if( ! isAppInDebugMode() )
            parent::__construct( message(
                "A technical error occurred on server! " .
                     "Probably you are doing something wrong. " .
                    "Please, inform the team." ), Response::HTTP_INTERNAL_SERVER_ERROR, $headers, $options);
        else
            parent::__construct([
                message( $e->getMessage() ),
                $e->getTrace()
            ], Response::HTTP_INTERNAL_SERVER_ERROR, $headers, $options);
    }
}
