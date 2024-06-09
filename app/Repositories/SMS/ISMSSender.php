<?php

namespace App\Repositories\SMS;

interface ISMSSender
{
    /**
     * @param string $to
     * @param string $message
     * @return bool
     */
    public function sendSMS(string $to, string $message):bool;
}
