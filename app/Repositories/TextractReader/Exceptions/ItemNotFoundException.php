<?php

namespace App\Repositories\TextractReader\Exceptions;

use Throwable;

class ItemNotFoundException extends \Exception
{

    //  {
    //    "detail": "Item does not exist"
    //  }

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct("Item does not exist. " . $message, $code, $previous);
    }
}
