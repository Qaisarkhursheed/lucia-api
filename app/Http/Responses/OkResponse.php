<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;


class OkResponse extends JsonResponse
{
    public function __construct($data = null,  $headers = [], $options = 0)
    {
        parent::__construct( $data, Response::HTTP_OK, $headers, $options);
    }
}
