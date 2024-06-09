<?php

namespace App\Traits;

use GuzzleHttp\Client;
use App\Models\Meeting;
use Log;

/**
 * trait ZoomMeetingTrait
 */
trait ZoomMeetingTrait
{
    public $client;
    public $jwt;
    public $headers;

    public function __construct()
    {

    }
    public function setUpCLient()
    {
        $this->client = new Client();
        $this->jwt = $this->generateZoomToken();
        $this->headers = [
            'Authorization' => 'Bearer '.$this->jwt,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ];
    }
    public function generateZoomToken()
    {
        $key = env('ZOOM_API_KEY', '');
        $secret = env('ZOOM_API_SECRET', '');
        $payload = [
            'iss' => $key,
            'exp' => strtotime('+1 minute'),
        ];

        return \Firebase\JWT\JWT::encode($payload, $secret, 'HS256');
    }

    private function retrieveZoomUrl()
    {
        return env('ZOOM_API_URL', '');
    }

    public function toZoomTimeFormat(string $dateTime)
    {
        try {
            $date = new \DateTime($dateTime);

            return $date->format('Y-m-d\TH:i:s');
        } catch (\Exception $e) {
            Log::error('ZoomJWT->toZoomTimeFormat : '.$e->getMessage());

            return '';
        }
    }

    public function create($data)
    {
        $this->setUpCLient();
        $this->client = new Client();
        $path = 'users/me/meetings';
        $url = $this->retrieveZoomUrl();

        $body = [
            'headers' => $this->headers,
            'body'    => json_encode([
                'topic'      => $data['topic'],
                // // "schedule_for" =>'Qaisar',
                // "contact_name"=>'Qaisar',
                // "contact_email"=>'qaisarmughal69@gmail.com',
                'type'       => self::MEETING_TYPE_SCHEDULE,
                'start_time' => $this->toZoomTimeFormat($data['start_time']),
                'duration'   => $data['duration'],
                'agenda'     => (! empty($data['agenda'])) ? $data['agenda'] : null,
                'timezone'     => 'Asia/Kolkata',
                'settings'   => [
                    'host_video'        => ($data['host_video'] == "1") ? true : false,
                    'participant_video' => ($data['participant_video'] == "1") ? true : false,
                    'waiting_room'      => false,
                    "audio" => "both",
                    "auto_recording"=>"none",
                    "join_before_host"=>true,
                ],
            ]),
        ];
        $response =  $this->client->post($url.$path, $body);
        $createdMeeting = json_decode($response->getBody(), true);
        \Log::info((json_encode($createdMeeting)));
        if($response->getStatusCode() == 201)
        {
            $meeting = new Meeting();
            $meeting->user_id = auth()->user()->id;
            $meeting->meeting_id = $createdMeeting['id'];
            $meeting->advisor_request_id = $data['advisor_request_id'];
            $meeting->topic = isset($createdMeeting['topic']) ? $createdMeeting['topic'] : null;
            $meeting->type = isset($createdMeeting['type']) ? $createdMeeting['type'] : null;
            $meeting->start_time = isset($createdMeeting['start_time']) ? $createdMeeting['start_time'] : null;
            $meeting->end_time = isset($createdMeeting['end_time']) ? $createdMeeting['end_time'] : null;
            $meeting->start_url = isset($createdMeeting['start_url']) ? json_encode($createdMeeting['start_url']) : null;
            $meeting->join_url = isset($createdMeeting['join_url']) ? json_encode($createdMeeting['join_url']) : null;
            $meeting->status = isset($createdMeeting['status']) ? $createdMeeting['status'] : null;
            $meeting->agenda = isset($createdMeeting['agenda']) ? $createdMeeting['agenda'] : null;
            $meeting->pre_schedule = isset($createdMeeting['pre_schedule']) ? $createdMeeting['pre_schedule'] : null;
            $meeting->save();
            return [
                'success' => $response->getStatusCode() === 201,
                'data'    => $createdMeeting,
                'meeting_id' => $meeting->id,
            ];
        }
        return [
            'success' => false,
            'errorMessage'    => "We are not able to create the meeting at this time sorry. Thank you!",
        ];

    }
    public function update($id, $data)
    {
        $path = 'meetings/'.$id;
        $url = $this->retrieveZoomUrl();

        $body = [
            'headers' => $this->headers,
            'body'    => json_encode([
                'topic'      => $data['topic'],
                'type'       => self::MEETING_TYPE_SCHEDULE,
                'start_time' => $this->toZoomTimeFormat($data['start_time']),
                'duration'   => $data['duration'],
                'agenda'     => (! empty($data['agenda'])) ? $data['agenda'] : null,
                'timezone'     => 'Asia/Kolkata',
                'settings'   => [
                    'host_video'        => ($data['host_video'] == "1") ? true : false,
                    'participant_video' => ($data['participant_video'] == "1") ? true : false,
                    'waiting_room'      => true,
                ],
            ]),
        ];
        $response =  $this->client->patch($url.$path, $body);

        return [
            'success' => $response->getStatusCode() === 204,
            'data'    => json_decode($response->getBody(), true),
        ];
    }

    public function get(Request $request)
    {

        $path = 'meetings/'.$request->meeting_id;
        $url = $this->retrieveZoomUrl();
        $this->jwt = $this->generateZoomToken();
        $body = [
            'headers' => $this->headers,
            'body'    => json_encode([]),
        ];

        $response =  $this->client->get($url.$path, $body);

        return [
            'success' => $response->getStatusCode() === 204,
            'data'    => json_decode($response->getBody(), true),
        ];
    }

    /**
     * @param string $id
     *
     * @return bool[]
     */
    public function delete($meeting_id)
    {
        $this->setUpCLient();
        $this->client = new Client();
        $path = 'meetings/'.$meeting_id;
        $url = $this->retrieveZoomUrl();
        $body = [
            'headers' => $this->headers,
            'body'    => json_encode([]),
        ];

        $response =  $this->client->delete($url.$path, $body);
        \Log::info(json_encode($response));
        return [
            'success' => $response->getStatusCode() === 204,
        ];
    }
}
