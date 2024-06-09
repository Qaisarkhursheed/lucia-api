<?php

namespace App\ModelsExtended\Traits;

use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\IInteractsWithServiceSupplier;
use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\ISupplierCompatible;
use App\ModelsExtended\Itinerary;
use App\ModelsExtended\ItineraryStatus;
use App\ModelsExtended\User;
use App\Repositories\Calendars\GoogleCalendars\GoogleCalendarClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * @property Itinerary $itinerary
 */
trait BelongsToItineraryTrait
{
    public function getFolderStorageRelativePath(): string
    {
        return $this->getFolderStorageRelativePathUsingSector( 'general' );
    }

    /**
     * Get traceable folder path
     */
    public function getFolderStorageRelativePathUsingSector( string $sector ): string
    {
        return sprintf(
            "%s/%s/%s",
            $this->itinerary->getFolderStorageRelativePath(),
            $sector,
            $this->id
        );
    }

    public function itinerary()
    {
        return $this->belongsTo(Itinerary::class);
    }

    /**
     * @return string
     */
    public function titleWithItinerary(): string
    {
        return sprintf( "%s - %s", $this->itinerary->title(), $this->title() );
    }

    /**
     * @return string|null
     */
    public function travellerEmail(): ?string
    {
        return optional($this->itinerary->traveller->defaultEmail)->email;
    }

    /**
     * @return bool
     */
    public function canCreateCalendarEvent(): bool
    {
        return $this->travellerEmail() && $this->itinerary->status_id == ItineraryStatus::Accepted ;
    }

    /**
     * Focuses on day, all day
     *
     * @param Carbon $startDateTimeUTC
     * @param Carbon $endDateTimeUTC
     * @param bool $updateQuietly
     * @return $this
     */
    public function createCalendarEventDayForBooking(
        Carbon $startDateTimeUTC, Carbon $endDateTimeUTC,
        bool $updateQuietly = true
    )
    {
        try {
            $client = new GoogleCalendarClient( User::getCalendarSyncUser() );

            // no point creating with no traveller
            if( !$this->canCreateCalendarEvent() || !$client->getCanConnect() ) return $this;

            $event_id = $this->google_calendar_event_id;
            if( $event_id )
                $event_id = $client->updateEvent(
                    $event_id,
                    $this->titleWithItinerary(),
                    $startDateTimeUTC, $endDateTimeUTC,
                    $this->travellerEmail(),
                    $this->notes()
                );
            else
                $event_id = $client->createEvent(
                    $this->titleWithItinerary(),
                    $startDateTimeUTC, $endDateTimeUTC,
                    $this->travellerEmail(),
                    $this->notes()
                );

            $this->google_calendar_event_id = $event_id;

            if( $updateQuietly ) $this->updateQuietly();
        }catch (\Exception $exception)
        {
            Log::error( "error creating itinerary on google calendar. " . $exception->getMessage(), $exception->getTrace() );
        }
        return $this;
    }

    /**
     * Absorbs time as well
     *
     * @param Carbon $startDateTimeUTC
     * @param Carbon $endDateTimeUTC
     * @param bool $updateQuietly
     * @return $this
     */
    public function createCalendarEventReminderTimeForBooking(
        Carbon $startDateTimeUTC, Carbon $endDateTimeUTC,
        bool $updateQuietly = true
    )
    {
        try {
            $client = new GoogleCalendarClient( User::getCalendarSyncUser() );

            // no point creating with no traveller
            if( !$this->canCreateCalendarEvent() || !$client->getCanConnect() ) return $this;

            $event_id = $this->google_calendar_event_id;
            if( $event_id )
                $event_id = $client->updateReminderTimeEvent(
                    $event_id,
                    $this->titleWithItinerary(),
                    $startDateTimeUTC, $endDateTimeUTC,
                    $this->travellerEmail(),
                    $this->notes()
                );
            else
                $event_id = $client->createReminderTimeEvent(
                    $this->titleWithItinerary(),
                    $startDateTimeUTC, $endDateTimeUTC,
                    $this->travellerEmail(),
                    $this->notes()
                );

            $this->google_calendar_event_id = $event_id;

            if( $updateQuietly ) $this->updateQuietly();
        }catch (\Exception $exception)
        {
            Log::error( "error creating itinerary on google calendar. " . $exception->getMessage(), $exception->getTrace() );
        }
        return $this;
    }


    // only for those that can create supplier
    /**
     * @return ISupplierCompatible|null|IInteractsWithServiceSupplier
     */
    public function getSupplierAttribute():?ISupplierCompatible
    {
        $supplier_relation = $this->getSupplierRelation();
        if( $this->getSavedToLibrary() )
            return $supplier_relation->service_supplier?? $supplier_relation;

        return  $supplier_relation;
    }

    /**
     * @return IInteractsWithServiceSupplier|ISupplierCompatible|null
     */
    private function getSupplierRelation()
    {
        return $this->{$this->getSupplierRelationshipAttributeName()};
    }

    /**
     * @return bool
     */
    public function getSavedToLibrary(): bool{
        return  optional($this->getSupplierRelation())->save_to_library === true;
    }
}
