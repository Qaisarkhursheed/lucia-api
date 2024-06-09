<?php

namespace App\Http\Middleware;



use App\Http\Middleware\LaravelMiddleware\TransformsRequest;

class ConvertEmptyStringToNullMiddleware extends TransformsRequest
{
  protected function transform($key, $value)
  {
    if($value === '')
      return null;

    return $value;
  }
}
