<?php

namespace App\ModelsExtended;

use App\ModelsExtended\Interfaces\IBookingModelInterface;
use App\ModelsExtended\Interfaces\ICanCreateGoogleCalendarEventInterface;
use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;
use App\ModelsExtended\Interfaces\IShiftableBookingInterface;
use App\ModelsExtended\Traits\BelongsToItineraryTrait;
use App\ModelsExtended\Traits\CanCreateGoogleCalendarEventTrait;
use App\ModelsExtended\Traits\ItineraryBookingsSummableTrait;
use App\ModelsExtended\Traits\ShareableSortablePackagerTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property bool $is_multi_leg
 * @property int $total_duration_in_minutes
 * @property Collection|ItineraryFlightSegment[] $itinerary_flight_segments
 * @property Collection|FlightPassenger[] $flight_passengers
 * @property ItineraryFlightSegment|null $earliest_flight
 * @property ItineraryFlightSegment|null $last_flight
 */
class ItineraryFlight extends \App\Models\ItineraryFlight
    implements IBookingModelInterface,  ICanCreateGoogleCalendarEventInterface, IDeveloperPresentationInterface
{
    use BelongsToItineraryTrait, ItineraryBookingsSummableTrait,
        ShareableSortablePackagerTrait, CanCreateGoogleCalendarEventTrait;

    public $replicableRelations = [
        "flight_passengers",
        "flight_pictures",
//        "flight_supplier", // we don't use flight_supplier any more
        "itinerary_flight_segments",
    ];
    protected $appends = ['departure_datetime_locale', 'arrival_datetime_locale',  ];
    protected $with = ['earliest_flight'];

    public function flight_pictures()
    {
        return $this->hasMany(FlightPicture::class);
    }

    public function flight_passengers()
    {
        return $this->hasMany(FlightPassenger::class);
    }

    /**
     * get earliest flight segment real state
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function earliest_flight()
    {
        return $this->hasOne(ItineraryFlightSegment::class)
            ->oldest('departure_datetime');
    }

    /**
     * get earliest flight segment real state
     * if this is round trip it returns same info
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function last_flight()
    {
        return $this->hasOne(ItineraryFlightSegment::class)
            ->latest('departure_datetime');
    }

    public function itinerary_flight_segments()
    {
        return $this->hasMany(ItineraryFlightSegment::class);
    }

    /**
     * Get traceable folder path
     */
    public function getFolderStorageRelativePath(): string
    {
        return $this->getFolderStorageRelativePathUsingSector('flights');
    }

    // Implement time zone reversal
    public function getDepartureDatetimeLocaleAttribute()
    {
        return optional($this->earliest_flight)->getDepartureDatetimeLocaleAttribute();
    }

    public function getArrivalDatetimeLocaleAttribute()
    {
        return optional($this->earliest_flight)->getArrivalDatetimeLocaleAttribute();
    }

    public function getIsMultiLegAttribute()
    {
        return $this->itinerary_flight_segments->count()>1;
    }

    public function getTotalDurationInMinutesAttribute()
    {
        return $this->itinerary_flight_segments->sum( function( ItineraryFlightSegment $segment){
            return ($segment->duration_in_minutes??0) + ($segment->calculateLayoverInMinutes()??0);
        });
    }

    /**
     * @inheritDoc
     */
    public function formatForSharing(): array
    {
        return [
            'title' => $this->title(),

            'start_date' => $this->displayDayDateFormatUTC(optional($this->earliest_flight)->departure_datetime),
            'start_date_locale' => $this->getDepartureDatetimeLocaleAttribute(),

            'end_date' => $this->displayDayDateFormatUTC(optional($this->earliest_flight)->arrival_datetime),
            'end_date_locale' => $this->getArrivalDatetimeLocaleAttribute(),

            'pictures' => $this->flight_pictures->pluck('image_url'),

            'from' => optional($this->earliest_flight)->flight_from,
            'from_iata' => optional($this->earliest_flight)->flight_from_iata,

            'to' => optional($this->last_flight)->flight_to,
            'to_iata' => optional($this->last_flight)->flight_to_iata,

            'is_multi_leg' => $this->is_multi_leg,
            'total_duration_in_minutes' => $this->total_duration_in_minutes,

            'segments' => $this->itinerary_flight_segments->map->formatForSharing(),

            'passengers' => $this->flight_passengers->map->presentForDev(),

            'check_in_url' => $this->check_in_url,

            'price' => $this->itinerary->show_price_on_share ? $this->price : null,

            'confirmation_number' => $this->confirmation_number,
            'notes' => $this->notes,
            "cancel_policy" => $this->cancel_policy,
            "id" => $this->id,

            'itinerary_id'  => $this->itinerary_id,
            'booking_category_id'  => $this->booking_category_id,
            'custom_header_title'  => $this->custom_header_title,
            'google_calendar_event_id'  => $this->google_calendar_event_id,
            'sorting_rank'  => $this->sorting_rank,
        ];
    }

    /**
     * @inheritDoc
     */
    public function sortByKey(): Carbon
    {
        return $this->getDateTimeLocale(optional($this->earliest_flight)->departure_datetime) ?? Carbon::now();
    }

    // to array will cause recursive error
    public function toArray()
    {
        return $this->presentForDev();
    }

    /**
     * @inheritDoc
     */
    public function moveStartDate(Carbon $newDateLocale): IShiftableBookingInterface
    {
        if (!$this->earliest_flight || $this->itinerary_flight_segments->count() > 1) return $this;

        $daysDifference = $this->earliest_flight->departure_datetime->diffInDays($this->earliest_flight->arrival_datetime);

        $this->earliest_flight->departure_datetime = $newDateLocale->clone()->setTimeFrom($this->earliest_flight->getDepartureDatetimeLocaleAttribute())->fromPreferredTimezoneToAppTimezone();
        $this->earliest_flight->arrival_datetime = $this->earliest_flight->departure_datetime->addDays($daysDifference);

        $this->earliest_flight->updateQuietly();

        return $this;
    }

    public function title(): ?string
    {
        return $this->custom_header_title ?? 'Flight ';
    }

    public function notes(): ?string
    {
        return sprintf("%s - %s\n %s", $this->flight_number, $this->flight_from, $this->notes);
    }

    /**
     * @inheritDoc
     */
    public function createCalendarEvent(bool $updateQuietly = true)
    {
        if (!$this->earliest_flight) return $this;

        return $this->createCalendarEventReminderTimeForBooking($this->earliest_flight->departure_datetime, $this->earliest_flight->arrival_datetime, $updateQuietly);
    }


}
