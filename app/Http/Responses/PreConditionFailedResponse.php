<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;


/**
 * Use for validating request parameters
 * Class PreConditionFailedResponse
 * @package App\Http\Responses
 */
class PreConditionFailedResponse extends JsonResponse
{
    public function __construct($data = null,  $headers = [], $options = 0)
    {
        parent::__construct($data, Response::HTTP_PRECONDITION_FAILED, $headers, $options);
    }
}
