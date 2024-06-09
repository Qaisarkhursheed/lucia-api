<?php

namespace App\Exceptions;

class RecordNotFoundException extends \Exception
{
    public function __construct()
    {
        parent::__construct( "The record does not exists!" );
    }
}
