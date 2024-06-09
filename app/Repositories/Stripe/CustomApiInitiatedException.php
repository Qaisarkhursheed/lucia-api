<?php

namespace App\Repositories\Stripe;


use Throwable;

class CustomApiInitiatedException extends \Stripe\Exception\ApiErrorException
{
    public function __construct($message = "", array $returnStack = [])
    {
        parent::__construct($message, 0, null);
        $this->setJsonBody($returnStack);
    }
}
