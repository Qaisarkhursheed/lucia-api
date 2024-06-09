<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\Validator;


/**
 * Use for validating request parameters
 * Class PreConditionFailedResponse
 * @package App\Http\Responses
 */
class ExpectionFailedResponse extends JsonResponse
{
    public function __construct($data = null,  $headers = [], $options = 0)
    {
        parent::__construct($data, Response::HTTP_EXPECTATION_FAILED, $headers, $options);
    }

	public static function createResponse( Validator $validator)
	{
		return new self( $validator->getMessageBag()->all() );
    }
}
