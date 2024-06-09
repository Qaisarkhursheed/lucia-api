<?php

namespace App\ModelsExtended;

use App\ModelsExtended\Interfaces\IBookingModelInterface;
use App\ModelsExtended\Interfaces\ICanCreateGoogleCalendarEventInterface;
use App\ModelsExtended\Interfaces\ICanCreateServiceProviderInterface;
use App\ModelsExtended\Interfaces\IShiftableBookingInterface;
use App\ModelsExtended\Traits\BelongsToItineraryTrait;
use App\ModelsExtended\Traits\CanCreateGoogleCalendarEventTrait;
use App\ModelsExtended\Traits\ItineraryBookingsSummableTrait;
use App\ModelsExtended\Traits\ShareableSortablePackagerTrait;
use Carbon\Carbon;

class ItineraryTransport extends \App\Models\ItineraryTransport
    implements ICanCreateServiceProviderInterface, IBookingModelInterface, ICanCreateGoogleCalendarEventInterface
{
    use BelongsToItineraryTrait, ItineraryBookingsSummableTrait, ShareableSortablePackagerTrait, CanCreateGoogleCalendarEventTrait;

    public $replicableRelations = [
        "transport_passengers",
        "transport_pictures",
        "transport_supplier",
    ];

    protected $appends = [ 'departure_datetime_locale', 'arrival_datetime_locale' ];

    public function transport_pictures()
    {
        return $this->hasMany(TransportPicture::class);
    }

    public function transport_supplier()
    {
        return $this->hasOne(TransportSupplier::class);
    }

    /**
     * Get traceable folder path
     */
    public function getFolderStorageRelativePath(): string
    {
        return $this->getFolderStorageRelativePathUsingSector( 'transports' );
    }

    // Implement time zone reversal
    public function getDepartureDatetimeLocaleAttribute()
    {
        return $this->departure_datetime->fromAppTimezoneToUserPreferredTimezone($this->itinerary->user);
    }

    public function getArrivalDatetimeLocaleAttribute()
    {
        return $this->arrival_datetime->fromAppTimezoneToUserPreferredTimezone($this->itinerary->user);
    }

    /**
     * @inheritDoc
     */
    public function getSupplierRelationshipAttributeName(): string
    {
        return 'transport_supplier';
    }

    /**
     * @inheritDoc
     */
    public function formatForSharing(): array
    {
        $supplier = $this->getSupplierAttribute();

        return [

            "id" => $this->id,
            "sorting_rank" => $this->sorting_rank,

            "transit_type_id" => $this->transit_type_id,
            "transit_type" => $this->transit_type->description,

            "booking_category_id" => $this->booking_category_id,
            "custom_header_title" => $this->custom_header_title,
            "save_to_library" => $this->getSavedToLibrary(),

            'title' => $this->title(),
            'address' => optional($supplier)->getAddress(),
            'phone' => optional($supplier)->getPhone(),

            'pictures' => presentCollectionForDev(optional($supplier)->getPictures()),
            "supplier" => optional($supplier)->presentForDev(),

            'price' => $this->getSum(),

            'departure_datetime' => $this->displayDayDateFormatUTC( $this->departure_datetime ),
            'arrival_datetime' => $this->displayDayDateFormatUTC( $this->arrival_datetime ),

            'departure_datetime_locale' => $this->getDateTimeLocale( $this->departure_datetime ),
            'arrival_datetime_locale' => $this->getDateTimeLocale( $this->arrival_datetime ),

            'transport_from' => $this->transport_from,
            'transport_to' => $this->transport_to,
            'vehicle' => $this->vehicle,

            'notes' => $this->notes(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function sortByKey(): Carbon
    {
        return $this->getDateTimeLocale( $this->departure_datetime );
    }

    /**
     * @inheritDoc
     */
    public function moveStartDate(Carbon $newDateLocale ): IShiftableBookingInterface
    {
        $daysDifference =  $this->departure_datetime->diffInDays( $this->arrival_datetime );

        $this->departure_datetime = $newDateLocale->clone()->setTimeFrom( $this->getDepartureDatetimeLocaleAttribute() )->fromPreferredTimezoneToAppTimezone();
        $this->arrival_datetime = $this->departure_datetime->addDays( $daysDifference );

        return $this;
    }

    public function title(): ?string
    {
        // ?? coalesce feature fails if it is not null and if it is empty string.
        return  empty( $this->custom_header_title ) ? optional($this->getSupplierAttribute())->name : $this->custom_header_title;
    }

    public function notes(): ?string
    {
        return optional($this->getSupplierAttribute())->description;
    }

    /**
     * @inheritDoc
     */
    public function createCalendarEvent(bool $updateQuietly = true)
    {
        return $this->createCalendarEventReminderTimeForBooking( $this->departure_datetime, $this->arrival_datetime, $updateQuietly );
    }

    public function calendarEndDate(): Carbon
    {
        return $this->getArrivalDatetimeLocaleAttribute() ?? $this->sortByKey();
    }
}
