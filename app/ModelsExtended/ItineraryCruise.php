<?php

namespace App\ModelsExtended;

use App\ModelsExtended\Interfaces\IBookingModelInterface;
use App\ModelsExtended\Interfaces\ICanCreateGoogleCalendarEventInterface;
use App\ModelsExtended\Interfaces\ICanCreateServiceProviderInterface;
use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;
use App\ModelsExtended\Interfaces\IDoNotCreateGlobalSupplierInterface;
use App\ModelsExtended\Interfaces\IShiftableBookingInterface;
use App\ModelsExtended\Traits\BelongsToItineraryTrait;
use App\ModelsExtended\Traits\CanCreateGoogleCalendarEventTrait;
use App\ModelsExtended\Traits\ItineraryBookingsSummableTrait;
use App\ModelsExtended\Traits\ShareableSortablePackagerTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property Collection|CruiseCabin[] $cruise_cabins
 * @property Collection|CruisePassenger[] $cruise_passengers
 * @property Collection|CruisePicture[] $cruise_pictures
 */
class ItineraryCruise extends \App\Models\ItineraryCruise
    implements IBookingModelInterface,
    ICanCreateServiceProviderInterface, ICanCreateGoogleCalendarEventInterface
{
    use BelongsToItineraryTrait, ItineraryBookingsSummableTrait, ShareableSortablePackagerTrait, CanCreateGoogleCalendarEventTrait;

    protected $appends = [ 'departure_datetime_locale', 'disembarkation_datetime_locale' ];

    public $doNotReplicateProperties = [
        'google_calendar_event_id'
    ];

    public $replicableRelations = [
        'cruise_pictures',
        'cruise_passengers',
        'cruise_supplier',
    ];

    public function cruise_cabins()
    {
        return $this->hasMany(CruiseCabin::class);
    }

    public function cruise_pictures()
    {
        return $this->hasMany(CruisePicture::class);
    }

    public function cruise_supplier()
    {
        return $this->hasOne(CruiseSupplier::class);
    }

    public function cruise_passengers()
    {
        return $this->hasMany(CruisePassenger::class);
    }

    /**
     * Get traceable folder path
     */
    public function getFolderStorageRelativePath(): string
    {
        return $this->getFolderStorageRelativePathUsingSector( 'cruises' );
    }

    public function getDepartureDatetimeLocaleAttribute()
    {
        return $this->departure_datetime->fromAppTimezoneToPreferredTimezone();
    }

    public function getDisembarkationDatetimeLocaleAttribute()
    {
        return $this->disembarkation_datetime->fromAppTimezoneToPreferredTimezone();
    }

    /**
     * @inheritDoc
     */
    public function getSupplierRelationshipAttributeName(): string
    {
        return 'cruise_supplier';
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

            "booking_category_id" => $this->booking_category_id,
            "custom_header_title" => $this->custom_header_title,
            "save_to_library" => $this->getSavedToLibrary(),

            'title' => $this->title(),
            'address' => $supplier->getAddress(),
            'phone' => $supplier->getPhone(),

            'pictures' => $supplier->getPictures()->map->presentForDev(),
            "supplier" => $supplier->presentForDev(),

            "cabins" => $this->cruise_cabins->map->presentForDev(),
            "passengers" => $this->cruise_passengers->map->presentForDev(),

            "departure_port_city" => $this->departure_port_city,
            "arrival_port_city" => $this->arrival_port_city,

            'from_geolocation'=> [
                'latitude' => $this->departure_latitude,
                'longitude' => $this->departure_longitude,
            ],

            'to_geolocation'=> [
                'latitude' => $this->arrival_latitude,
                'longitude' => $this->arrival_longitude,
            ],

            'departure_datetime' => $this->displayDayDateFormatUTC( $this->departure_datetime ),
            'disembarkation_datetime' => $this->displayDayDateFormatUTC( $this->disembarkation_datetime ),

            'departure_datetime_locale' => $this->getDateTimeLocale( $this->departure_datetime ),
            'disembarkation_datetime_locale' => $this->getDateTimeLocale( $this->disembarkation_datetime ),

            'notes' => $this->notes(),
            'cancel_policy' => $this->cancel_policy,

            'cruise_ship_name' => $this->cruise_ship_name,
            'departure_longitude' => $this->departure_longitude,
            'departure_latitude' => $this->departure_latitude,
            'arrival_longitude' => $this->arrival_longitude,
            'arrival_latitude' => $this->arrival_latitude,
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
        $daysDifference =  $this->departure_datetime->diffInDays( $this->disembarkation_datetime );

        $this->departure_datetime = $newDateLocale->clone()->setTimeFrom( $this->getDepartureDatetimeLocaleAttribute() )->fromPreferredTimezoneToAppTimezone();
        $this->disembarkation_datetime = $this->departure_datetime->addDays( $daysDifference );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function createCalendarEvent(bool $updateQuietly = true)
    {
        return $this->createCalendarEventReminderTimeForBooking( $this->departure_datetime, $this->disembarkation_datetime, $updateQuietly );
    }

    public function calendarEndDate(): Carbon
    {
        return $this->getDisembarkationDatetimeLocaleAttribute() ?? $this->sortByKey();
    }
}
