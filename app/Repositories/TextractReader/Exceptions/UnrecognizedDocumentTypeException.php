<?php

namespace App\Repositories\TextractReader\Exceptions;

use Throwable;

class UnrecognizedDocumentTypeException extends \Exception
{
    public function __construct(string $category, Throwable $previous = null)
    {
        parent::__construct("The document type did not match any document type under " . $category, 0, $previous);
    }
}
