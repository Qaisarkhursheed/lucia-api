<?php


namespace App\Exceptions;


use Exception;
use Illuminate\Support\Str;
use Throwable;

class DuplicationDBRecordException extends Exception
{
    const MSG = "Sorry, the record can not be inserted because the same record already exist";

    public function __construct($message = self::MSG, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @throws DuplicationDBRecordException
     */
    public static function detectAndThrow(Exception $exception)
    {
        if (Str::contains($exception->getMessage(), "Duplicate entry"))
            throw new self(self::MSG, 0, $exception);
    }
}
