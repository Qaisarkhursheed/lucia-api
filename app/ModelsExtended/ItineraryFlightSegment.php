<?php

namespace App\ModelsExtended;

use App\ModelsExtended\Interfaces\ICanCreateGoogleCalendarEventInterface;
use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;
use App\ModelsExtended\Interfaces\IShareableRenderInterface;
use App\ModelsExtended\Traits\ItineraryBookingsSummableTrait;
use Carbon\Carbon;

/**
 * @property Carbon $arrival_datetime_locale
 * @property Carbon $departure_datetime_locale
 * @property Itinerary $itinerary
 * @property ItineraryFlight $itinerary_flight
 * @property ItineraryFlightSegment|null $next_flight_segment
 */
class ItineraryFlightSegment extends \App\Models\ItineraryFlightSegment
    implements IShareableRenderInterface, ICanCreateGoogleCalendarEventInterface,
    IDeveloperPresentationInterface
{
    use ItineraryBookingsSummableTrait;

    protected $appends = [ 'departure_datetime_locale', 'arrival_datetime_locale' ];

    public function itinerary_flight()
    {
        return $this->belongsTo(ItineraryFlight::class);
    }

    /**
     * get next flight segment lazy loading
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getNextFlightSegmentAttribute()
    {
        return self::query()
            ->where("id", ">", $this->id)
            ->where("itinerary_flight_id",  $this->itinerary_flight_id)
            ->oldest('id')
            ->get()
            ->first();
    }

    /**
     * @return int|null
     */
    public function calculateLayoverInMinutes(): ?int
    {
        if( !$this->next_flight_segment ) return null;
        return $this->next_flight_segment->departure_datetime_locale->diffInMinutes( $this->arrival_datetime_locale );
    }

    // Implement time zone reversal
    public function getDepartureDatetimeLocaleAttribute()
    {
        if( !$this->itinerary ) return null;
        return $this->departure_datetime->fromAppTimezoneToUserPreferredTimezone($this->itinerary->user);
    }

    public function getArrivalDatetimeLocaleAttribute()
    {
        if( !$this->itinerary ) return null;
        return $this->arrival_datetime->fromAppTimezoneToUserPreferredTimezone($this->itinerary->user );
    }

    public function getItineraryAttribute()
    {
        return optional($this->itinerary_flight)->itinerary;
    }

    /**
     * @inheritDoc
     */
    public function formatForSharing(): array
    {
        return [

            'start_date' => $this->displayDayDateFormatUTC( $this->departure_datetime ),
            'start_date_locale' => $this->departure_datetime_locale,

            'end_date' => $this->displayDayDateFormatUTC($this->arrival_datetime ),
            'end_date_locale' => $this->arrival_datetime_locale,

            'from' => $this->flight_from,
            'to' => $this->flight_to,

            'flight_from_iata'=> $this->flight_from_iata,
            'flight_from_icao'=> $this->flight_from_icao,
            'flight_from_latitude'=> $this->flight_from_latitude,
            'flight_from_longitude'=> $this->flight_from_longitude,

            'flight_to_iata'=> $this->flight_to_iata,
            'flight_to_icao'=> $this->flight_to_icao,
            'flight_to_latitude'=> $this->flight_to_latitude,
            'flight_to_longitude'=> $this->flight_to_longitude,

            'flight_number' => $this->flight_number,
            'airline' => $this->airline,
            'airline_operator' => $this->airline_operator,

            'duration_in_minutes' => $this->duration_in_minutes,
            'layover_in_minutes' => $this->calculateLayoverInMinutes(),
            'id' => $this->id,

        ];
    }

    /**
     * @inheritDoc
     */
    public function createCalendarEvent(bool $updateQuietly = true)
    {
        return $this->itinerary_flight->createCalendarEvent($updateQuietly);
    }

    /**
     * @inheritDoc
     */
    public function deleteCalendarEvent(bool $updateQuietly = true)
    {
        return $this->itinerary_flight->deleteCalendarEvent($updateQuietly);
    }

    /**
     * @inheritDoc
     */
    public function notes(): ?string
    {
       return $this->itinerary_flight->notes();
    }

    /**
     * @inheritDoc
     */
    public function title(): ?string
    {
        return $this->itinerary_flight->title();
    }

    /**
     * @inheritDoc
     */
    public function presentForDev(): array
    {
        return $this->formatForSharing();
    }

}
