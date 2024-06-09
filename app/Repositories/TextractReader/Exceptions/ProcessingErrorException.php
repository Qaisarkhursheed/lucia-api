<?php

namespace App\Repositories\TextractReader\Exceptions;

use Throwable;

class ProcessingErrorException extends \Exception
{
    //  {
    //    "detail": "Error processing file: script exited with message: Unable to locate credentials"
    //  }

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct("Error processing file " . $message, $code, $previous);
    }
}
