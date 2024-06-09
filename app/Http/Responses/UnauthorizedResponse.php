<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;


class UnauthorizedResponse extends JsonResponse
{
    public function __construct($data = null,  $headers = [], $options = 0)
    {
        parent::__construct($data, Response::HTTP_UNAUTHORIZED, $headers, $options);
    }
}
