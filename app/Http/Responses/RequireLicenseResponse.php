<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;


class RequireLicenseResponse extends JsonResponse
{
    public function __construct($data = null,  $headers = [], $options = 0)
    {
        parent::__construct($data, Response::HTTP_PAYMENT_REQUIRED, $headers, $options);
    }
}
