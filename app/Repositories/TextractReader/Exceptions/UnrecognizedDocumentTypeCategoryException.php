<?php

namespace App\Repositories\TextractReader\Exceptions;

use Throwable;

class UnrecognizedDocumentTypeCategoryException extends \Exception
{
    public function __construct(Throwable $previous = null)
    {
        parent::__construct("The document type did not match any document type category!", 0, $previous);
    }
}
