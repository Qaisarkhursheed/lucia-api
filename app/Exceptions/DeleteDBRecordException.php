<?php


namespace App\Exceptions;


use Exception;
use Illuminate\Support\Str;
use Throwable;

class DeleteDBRecordException extends Exception
{
    const MSG = "Sorry, the record can not be deleted because it is referenced in your application by other records!";

    public function __construct($message = self::MSG, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @throws DuplicationDBRecordException
     */
    public static function detectAndThrow(Exception $exception)
    {
        if (Str::contains($exception->getMessage(), "Cannot delete or update a parent row"))
            throw new self(self::MSG, 0, $exception);
    }
}
