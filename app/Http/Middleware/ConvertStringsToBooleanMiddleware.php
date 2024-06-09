<?php

namespace App\Http\Middleware;



use App\Http\Middleware\LaravelMiddleware\TransformsRequest;

class ConvertStringsToBooleanMiddleware extends TransformsRequest
{
  protected function transform($key, $value)
  {
    // Adjust booleans
    //
    if($value === 'true' || $value === 'TRUE')
      return true;

    if($value === 'false' || $value === 'FALSE')
      return false;


    return $value;
  }
}
