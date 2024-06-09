<?php

namespace App\Repositories\SMS;

use Aws\Result;
use Aws\Sns\SnsClient;
use Illuminate\Support\Facades\Log;

class AwsSNSTextMessage implements ISMSSender
{
    /**
     * @var array
     */
    private array $credentials;

    /**
     * @var SnsClient
     */
    private SnsClient $SNSHandler;

    /**
     * @var Result
     */
    private Result $lastResult;

    public function __construct()
    {
        $this->credentials = array(
            'credentials' => array(
                'key' => env( "AWS_SNS_ACCESS_KEY_ID" ),
                'secret' => env( "AWS_SNS_SECRET_ACCESS_KEY" ),
            ),
            'region' => env( "AWS_SNS_REGION" ), // < your aws from SNS Topic region
            'version' => 'latest'
        );
        $this->SNSHandler = new SnsClient($this->credentials);
    }

    /**
     * Enter the $to value like this +33666630760
     *
     * @inheritDoc
     */
    public function sendSMS(string $to, string $message): bool
    {
        try {

            $to = self::cleanUpPhoneNumber($to);

            $args = array(
                "MessageAttributes" => [
                    // You can put your senderId here. but first you have to verify the senderid by customer support of AWS then you can use your senderId.
                    // If you don't have senderId then you can comment senderId
//                 'AWS.SNS.SMS.SenderID' => [
//                     'DataType' => 'String',
//                     'StringValue' => 'Letslucia'
//                 ],
                    'AWS.SNS.SMS.SMSType' => [
                        'DataType' => 'String',
                        'StringValue' => 'Transactional'
                    ]
                ],
                "Message" => $message,
                "PhoneNumber" => $to   // Provide phone number with country code
            );

            $this->lastResult = $this->SNSHandler->publish($args);

            return true;
        }catch ( \Exception $exception){
            Log::error( "Problem sending SMS with AWS SNS", $exception->getTrace() );
            return false;
        }
    }

    /**
     * @return Result
     */
    public function getLastResult(): Result
    {
        return $this->lastResult;
    }

    public static function cleanUpPhoneNumber(string $to)
    {
        return "+" . preg_replace('/[^0-9_]/', '', $to );
    }
}
