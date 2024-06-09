<?php

namespace App\Repositories\Pusher;

class PusherBeam
{
    private \Pusher\PushNotifications\PushNotifications $beamsClient;

    public function __construct()
    {
        // https://github.com/pusher/push-notifications-php
        $this->beamsClient = new \Pusher\PushNotifications\PushNotifications(array(
            "instanceId" => env("PUSHER_BEAM_INSTANCE"),
            "secretKey" => env("PUSHER_BEAM_KEY"),
        ));
    }

    /**
     * @return \Pusher\PushNotifications\PushNotifications
     */
    public function client()
    {
        return $this->beamsClient;
    }

    /**
     * @param array|string[] $userIds
     * @param string $title
     * @param string|null $body
     * @return mixed
     * @throws \Exception
     */
    public function publishToUsers(array $userIds, string $title, ?string $body)
    {
        return  $this->client()->publishToUsers(
            $userIds, $this->createMessage($title, $body )
        );
    }

    /**
     * @param array|string[] $topics
     * @param string $title
     * @param string|null $body
     * @return mixed
     * @throws \Exception
     */
    public function publishToInterests(array $topics, string $title, ?string $body)
    {
        return  $this->client()->publishToInterests(
            $topics, $this->createMessage($title, $body )
        );
    }

    /**
     * @param string $title
     * @param string|null $body
     * @return array
     */
    public function createMessage(string $title, ?string $body): array
    {
        return [
            "web" => [
                "notification" => [
                    "title" => $title,
                    "body" => $body,
                    "icon" => myAssetUrl("logo-lucia-l-letter.png"),
                ],
            ],
            "apns" => [
                "aps" => [
                    "alert" => $title,
                ],
            ],
            "fcm" => [
                "notification" => [
                    "title" => $title,
                    "body" => $body,
                    "icon" => myAssetUrl("logo-lucia-l-letter.png"),
                ],
            ],
        ];
    }
}
