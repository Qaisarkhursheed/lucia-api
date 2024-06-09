<?php

namespace App\Repositories\Calendars\GoogleCalendars;

use App\ModelsExtended\User;
use Carbon\Carbon;
use Exception;
use Google\Service\Calendar\Event;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventAttendee;
use Google_Service_Calendar_EventDateTime;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LogicException;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 *  https://developers.google.com/calendar/api/guides/create-events#php
 *  https://developers.google.com/calendar/api/quickstart/php
 *
 *  https://developers.google.com/calendar/api/guides/recurringevents
 *  https://developers.google.com/calendar/api/v3/reference/events/update
 */
class GoogleCalendarClient
{
    /**
     *  Route accepting call back
     */
    public const CALL_BACK_URL_PATH = '/auth/google-calendar/oauth/callback';
    public const CALENDAR_ID = 'primary';
    public const DATE_FORMAT = 'Y-m-d';

    /**
     * @var string
     */
    private string $callBackUrl;

    /**
     * @var string
     */
    private string $goolge_calandar_secret_path;

    /**
     * @var array
     */
    private array $requiredScopes = [
        Google_Service_Calendar::CALENDAR,
        Google_Service_Calendar::CALENDAR_EVENTS,
        Google_Service_Calendar::CALENDAR_EVENTS_READONLY,
        Google_Service_Calendar::CALENDAR_READONLY
    ];

    /**
     * @var Google_Client
     */
    private Google_Client $client;

    /**
     * @var User
     */
    private User $user;

    /**
     * @param User|Authenticatable $user
     * @throws Exception
     */
    public function __construct( User $user)
    {
        $this->user = $user;

        $this->callBackUrl = Str::of( env( 'APP_URL' ) )->rtrim( "/" )
            . Str::of(self::CALL_BACK_URL_PATH )->start( "/");

        $this->goolge_calandar_secret_path = storage_path( 'app/client_secret_letslucia.json' ) ;

        if( ! file_exists( $this->goolge_calandar_secret_path ) )
            throw new Exception( 'please, upload google secret credentials to ' . $this->goolge_calandar_secret_path );

        $this->createClient();
    }

    /**
     * It just creates the client
     *
     * @throws \Google\Exception
     */
    private function createClient()
    {
        $this->client = new Google_Client();

        $this->client->setScopes($this->requiredScopes);

        $this->client->setAuthConfig($this->goolge_calandar_secret_path);

        $this->client->setAccessType('offline');        // offline access

        $this->client->setPrompt('select_account consent');

        $this->client->setRedirectUri($this->callBackUrl );

        $this->loadToken();
    }

    /**
     * Loads token or try to refresh and load if possible
     * @return void
     */
    private function loadToken(): void
    {
        if ($this->user->google_authentication_token != null ) {
            // expects array
            $this->client->setAccessToken($this->user->google_authentication_token);
        }

        // Check if it is expired
        // If there is no previous token or it's expired.
        if ($this->client->isAccessTokenExpired()) {

            // Refresh the token if possible, else fetch a new one.
            if ($this->client->getRefreshToken()) {
                $this->saveAccessToken(
                    $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken())
                );
            }else {
                $this->saveAccessToken(null);
            }
        }
    }

    /**
     * Returns full URL for authentication
     *
     * @return string
     */
    public function getOAuthenticationURL(string $redirect_url = '/'): string
    {

        $this->client->setState( $this->createOAuthState( $redirect_url ) );

        // Request authorization from the user.
        return $this->client->createAuthUrl();
    }

    /**
     * @param string $redirect_url
     * @return string
     */
    private function createOAuthState( string $redirect_url = '/' ): string
    {
        return base64_encode(
            json_encode(
                [
                    "user_id" => $this->user->id,
                    "redirect_url" => $redirect_url
                ]
            )
        );
    }

    /**
     * @param string $state
     * @return array
     */
    public static function decodeOAuthSate( string $state ): array
    {
        return (array) json_decode( base64_decode( $state ) );
    }


    /**
     * https://github.com/googleapis/google-api-php-client
     * Authentication with OAuth
     *
     * Assumes you have handled the [ int $user_id ]
     * passed in as the last route parameter and it is the
     * user of this class
     *
     * @return $this
     * @throws LogicException
     * @throws \InvalidArgumentException
     */
    public function handleOAuthCallBack(): GoogleCalendarClient
    {
        // Confirm user accepts all necessary scopes
        // breaks by space and check if all needed is sent
        $scopes = explode(" ", \request()->input('scope'));
        if (count(array_intersect($this->requiredScopes, $scopes)) !== count($this->requiredScopes))
            throw new LogicException("Please, make sure you accept all scopes.");

        $access_object = (object)$this->client->fetchAccessTokenWithAuthCode( \request()->input( 'code' ) ) ;

        if( optional( $access_object )->error) throw new \InvalidArgumentException( $access_object->error );

        return $this->saveAccessToken( $this->client->getAccessToken() );
    }

    /**
     * Saves $this->client->getAccessToken(); in user
     *
     * @param array|null $val
     * @return $this
     */
    private function saveAccessToken(?array $val): GoogleCalendarClient
    {
        $this->user->google_authentication_token = $val;
        $this->user->updateQuietly();
        return $this;
    }

    /**
     * Only indicates we have a token at least checked on creation that it is still
     * valid. Does not necessarily mean that it will work
     * @return bool
     */
    public function getCanConnect(): bool
    {
        return $this->user->google_authentication_token !== null;
    }

    /**
     * @return array|null
     */
    public function getAccessToken(): ?array
    {
        return $this->client->getAccessToken();
    }

    /**
     * @return Google_Service_Calendar
     * @throws Exception
     */
    private function getGoogleServiceCal(): Google_Service_Calendar
    {
        return new Google_Service_Calendar($this->client);
    }

    /**
     * In case User wants to disconnect account
     *
     * @return $this
     */
    public function revokeToken(): GoogleCalendarClient
    {
        try {

            // https://github.com/googleapis/google-api-php-client/blob/main/docs/oauth-web.md#create-authorization-credentials
            // #Revoking a token
            $this->client->revokeToken();

        } finally {
            return $this->saveAccessToken(null);
        }
    }

    /**
     * This will still return the item even if it is in the trash.
     * NOTE: that unauthorized will be taken care of if detected!
     *
     * @param string $eventId
     * @return Event|null
     * @throws Exception
     */
    public function findEvent(string  $eventId): ?Event
    {
        try {
            // doesn't throw exception or return null
            $event =  $this->getGoogleServiceCal()->events->get( self::CALENDAR_ID, $eventId );
            return $event->summary ? $event : null;

        }catch ( \Google\Service\Exception $exception){

            if( $exception->getCode() === ResponseAlias::HTTP_UNAUTHORIZED )
                $this->saveAccessToken(null) ; // Token not valid anymore.

            throw $exception;
        }
    }

    /**
     *  It only sends the event to trash.
     *  However, it will throw exception if it is already in trash or doesn't exist
     * NOTE: that unauthorized will be taken care of if detected!
     *
     * @param string $eventId
     * @return GoogleCalendarClient
     * @throws \Google\Service\Exception
     */
    public function deleteEvent(string $eventId): GoogleCalendarClient
    {
        try {

            $this->getGoogleServiceCal()->events->delete( self::CALENDAR_ID, $eventId );
        }
        catch ( \Google\Service\Exception $exception){

            if( $exception->getCode() === ResponseAlias::HTTP_UNAUTHORIZED )
                $this->saveAccessToken(null) ; // Token not valid anymore.

            throw $exception;
        }
        catch (Exception $exception)
        {
            Log::error( $eventId . ' not found!' );
        }

        return $this;
    }

    /**
     * It will try to update the event but will create new one if it doesn't exist
     * Returns event ID
     *
     * @param string $eventId
     * @param string $eventTitle
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string|null $travellerEmail
     * @param string|null $description
     * @return string
     * @throws \Google\Service\Exception
     * @throws Exception
     */
    public function updateEvent( string $eventId, string $eventTitle, Carbon $startDate, Carbon $endDate, ?string $travellerEmail, ?string $description): string
    {
        // Check if we need to create a new event.
        $event = $this->findEvent($eventId);
        if( !$event ) return $this->createEvent( $eventTitle, $startDate, $endDate, $travellerEmail, $description  );

        $start = new Google_Service_Calendar_EventDateTime();
        $start->setDate($startDate->format( self::DATE_FORMAT ));

        $end = new Google_Service_Calendar_EventDateTime();
        $end->setDate($endDate->addDay()->format( self::DATE_FORMAT ) );

        return $this->updateCalendarEvent( $eventId,  $eventTitle , $start, $end, $travellerEmail, $description );
    }

    /**
     * Returns event Id
     *
     * @param string $eventTitle
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string|null $travellerEmail
     * @param string|null $description
     * @return string
     * @throws \Google\Service\Exception
     */
    public function createEvent( string $eventTitle, Carbon $startDate, Carbon $endDate, ?string $travellerEmail, ?string $description ): string
    {
        $start = new Google_Service_Calendar_EventDateTime();
        $start->setDate($startDate->format( self::DATE_FORMAT ));

        $end = new Google_Service_Calendar_EventDateTime();
        $end->setDate($endDate->addDay()->format( self::DATE_FORMAT ) );

        return $this->createCalendarEvent( $eventTitle, $start, $end, $travellerEmail, $description );
    }

    /**
     * Returns event Id
     *
     * @param string $eventTitle
     * @param Carbon $startDateTimeUTC
     * @param Carbon $endDateTimeUTC
     * @param string|null $travellerEmail
     * @param string|null $description
     * @return string
     * @throws \Google\Service\Exception
     */
    public function createReminderTimeEvent(string $eventTitle, Carbon $startDateTimeUTC, Carbon $endDateTimeUTC, ?string $travellerEmail, ?string $description ): string
    {
        $start = new Google_Service_Calendar_EventDateTime();
        $start->setDateTime($startDateTimeUTC->toIso8601String());

        $end = new Google_Service_Calendar_EventDateTime();
        $end->setDateTime($endDateTimeUTC->toIso8601String() );

        return $this->createCalendarEvent( $eventTitle, $start, $end, $travellerEmail, $description );
    }

    /**
     * Returns event Id
     *
     * @param string $eventTitle
     * @param Carbon $startDateTimeUTC
     * @param Carbon $endDateTimeUTC
     * @param string|null $travellerEmail
     * @param string|null $description
     * @return string
     * @throws \Google\Service\Exception
     */
    public function updateReminderTimeEvent(  string $eventId, string $eventTitle, Carbon $startDateTimeUTC, Carbon $endDateTimeUTC, ?string $travellerEmail, ?string $description ): string
    {
        // Check if we need to create a new event.
        $event = $this->findEvent($eventId);
        if( !$event ) return $this->createReminderTimeEvent( $eventTitle, $startDateTimeUTC, $endDateTimeUTC, $travellerEmail, $description  );

        $start = new Google_Service_Calendar_EventDateTime();
        $start->setDateTime($startDateTimeUTC->toIso8601String());

        $end = new Google_Service_Calendar_EventDateTime();
        $end->setDateTime($endDateTimeUTC->toIso8601String() );

        return $this->updateCalendarEvent( $eventId, $eventTitle, $start, $end, $travellerEmail, $description );
    }

    /**
     * @param string $eventTitle
     * @param Google_Service_Calendar_EventDateTime $startDateTimeUTC
     * @param Google_Service_Calendar_EventDateTime $endDateTimeUTC
     * @param string|null $travellerEmail
     * @param string|null $description
     * @return mixed
     * @throws \Google\Service\Exception
     * @throws Exception
     */
    private function createCalendarEvent( string $eventTitle, Google_Service_Calendar_EventDateTime $startDateTimeUTC, Google_Service_Calendar_EventDateTime $endDateTimeUTC, ?string $travellerEmail, ?string $description )
    {
        try {

            return $this->getGoogleServiceCal()->events->insert(self::CALENDAR_ID,
                $this->createEventCore( $eventTitle, $startDateTimeUTC, $endDateTimeUTC, $travellerEmail, $description )
            )->getId();

        }catch ( \Google\Service\Exception $exception){

            if( $exception->getCode() === ResponseAlias::HTTP_UNAUTHORIZED )
                $this->saveAccessToken(null) ; // Token not valid anymore.

            throw $exception;
        }
    }

    /**
     * @param string $eventTitle
     * @param Google_Service_Calendar_EventDateTime $startDateTimeUTC
     * @param Google_Service_Calendar_EventDateTime $endDateTimeUTC
     * @param string|null $travellerEmail
     * @param string|null $description
     * @return mixed
     * @throws \Google\Service\Exception
     * @throws Exception
     */
    private function updateCalendarEvent( string $eventId, string $eventTitle, Google_Service_Calendar_EventDateTime $startDateTimeUTC, Google_Service_Calendar_EventDateTime $endDateTimeUTC, ?string $travellerEmail, ?string $description )
    {
        try {


            // Status of the event. Optional. Possible values are:
            //  "confirmed" - The event is confirmed. This is the default status.
            //  "tentative" - The event is tentatively confirmed.
            //  "cancelled" - The event is cancelled (deleted).
            // The list method returns cancelled events only on
            // incremental sync (when syncToken or updatedMin are specified)
            // or if the showDeleted flag is set to true. The get method always returns them.

            $event = $this->createEventCore( $eventTitle, $startDateTimeUTC, $endDateTimeUTC, $travellerEmail, $description );

            $event->setStatus( "confirmed" );
            return $this->getGoogleServiceCal()->events->update(self::CALENDAR_ID, $eventId, $event)->getId();

        }catch ( \Google\Service\Exception $exception){

            if( $exception->getCode() === ResponseAlias::HTTP_UNAUTHORIZED )
                $this->saveAccessToken(null) ; // Token not valid anymore.

            throw $exception;
        }
    }

    /**
     * @param string $eventTitle
     * @param Google_Service_Calendar_EventDateTime $startDateTimeUTC
     * @param Google_Service_Calendar_EventDateTime $endDateTimeUTC
     * @param string|null $travellerEmail
     * @param string|null $description
     * @return Google_Service_Calendar_Event
     */
    private function createEventCore( string $eventTitle, Google_Service_Calendar_EventDateTime $startDateTimeUTC, Google_Service_Calendar_EventDateTime $endDateTimeUTC, ?string $travellerEmail, ?string $description ): Google_Service_Calendar_Event
    {
        $event = new Google_Service_Calendar_Event();

        $event->setSummary($eventTitle );
        $event->setDescription( $description? strip_tags( $description ) : null );


        $event->setStart($startDateTimeUTC);

        $event->setEnd($endDateTimeUTC);

//            $event->setGuestsCanModify(true);
        $event->setGuestsCanInviteOthers(true);
        $event->setGuestsCanSeeOtherGuests(true);

        if( $travellerEmail )
        {
            $attendee1 = new Google_Service_Calendar_EventAttendee();
            $attendee1->setEmail($travellerEmail );
            $attendees = array($attendee1);
            $event->setAttendees($attendees);
        }

        return $event;
    }

//    /**
//     * @param GoogleCalendarClient $client
//     * @return array|string
//     * @throws Exception
//     */
//    private function listEvents(GoogleCalendarClient $client)
//    {
//
//        // Get the API client and construct the service object.
//        $service = $client->getGoogleServiceCal();
//
//        // Print the next 10 events on the user's calendar.
//        $calendarId = 'primary';
//        $optParams = array(
//            'maxResults' => 10,
//            'orderBy' => 'startTime',
//            'singleEvents' => true,
//            'timeMin' => date('c'),
//        );
//        $results = $service->events->listEvents($calendarId, $optParams);
//        $events = $results->getItems();
//
//        dd( $events );
//        if (empty($events)) {
//            return "No upcoming events found.\n";
//        } else {
//            $collect = [];
//            $collect[] = "Upcoming events:\n";
//            foreach ($events as $event) {
//                $start = $event->start->dateTime;
//                if (empty($start)) {
//                    $start = $event->start->date;
//                }
//                $collect[] = sprintf("%s (%s)\n", $event->getSummary(), $start);
//            }
//            return $collect;
//        }
//    }

}
