<?php

namespace App\ModelsExtended\Traits;

use App\ModelsExtended\User;
use App\Repositories\Calendars\GoogleCalendars\GoogleCalendarClient;
use Illuminate\Support\Facades\Log;

/**
 * Implements ICanCreateGoogleCalendarEventInterface partially
 * @property string $google_calendar_event_id
 */
trait CanCreateGoogleCalendarEventTrait
{
    /**
     * @inheritDoc
     */
    public function deleteCalendarEvent(bool $updateQuietly = true)
    {
        try {

            $client = new GoogleCalendarClient( User::getCalendarSyncUser() );
            if( !$this->google_calendar_event_id || !$client->getCanConnect() ) return $this;

            $client->deleteEvent($this->google_calendar_event_id);

            $this->google_calendar_event_id = null;
            if ($updateQuietly) $this->updateQuietly();

        } catch (\Exception $exception) {
            Log::error("error deleting itinerary on google calendar. " . $exception->getMessage(), $exception->getTrace());
        }
        return $this;
    }
}
