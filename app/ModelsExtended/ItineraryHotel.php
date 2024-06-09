<?php

namespace App\ModelsExtended;

use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\ISupplierCompatible;
use App\ModelsExtended\Interfaces\IBookingModelInterface;
use App\ModelsExtended\Interfaces\ICanCreateGoogleCalendarEventInterface;
use App\ModelsExtended\Interfaces\ICanCreateServiceProviderInterface;
use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;
use App\ModelsExtended\Interfaces\IShiftableBookingInterface;
use App\ModelsExtended\Traits\BelongsToItineraryTrait;
use App\ModelsExtended\Traits\CanCreateGoogleCalendarEventTrait;
use App\ModelsExtended\Traits\ItineraryBookingsSummableTrait;
use App\ModelsExtended\Traits\ShareableSortablePackagerTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property Collection|HotelRoom[] $hotel_rooms
 * @property HotelSupplier $hotel_supplier
 * @property Itinerary $itinerary
 */
class ItineraryHotel extends \App\Models\ItineraryHotel
    implements IBookingModelInterface, ICanCreateServiceProviderInterface,
    ICanCreateGoogleCalendarEventInterface
{
    use BelongsToItineraryTrait, ItineraryBookingsSummableTrait, ShareableSortablePackagerTrait, CanCreateGoogleCalendarEventTrait;

    protected $appends = [
        'check_in_datetime_locale', 'check_out_datetime_locale',
        'check_in_datetime', 'check_out_datetime'
    ];

    public $replicableRelations = [
        "hotel_amenities",
        "hotel_passengers",
        "hotel_pictures",
        "hotel_rooms",
        "hotel_supplier",
    ];

    /**
     * @return Carbon
     */
    public function getCheckInDatetimeLocaleAttribute()
    {
        if( $this->check_in_date && $this->check_in_time )
            return $this->check_in_date->setTimeFromTimeString( $this->check_in_time )
                ->shiftTimezone($this->itinerary->user->getTimezone());

        return $this->check_in_date->shiftTimezone($this->itinerary->user->getTimezone());
    }

    public function getCheckInDatetimeAttribute()
    {
        return $this->getCheckInDatetimeLocaleAttribute()->fromUserPreferredTimezoneToAppTimezone($this->itinerary->user);
    }

    public function getCheckOutDatetimeLocaleAttribute()
    {
        if( $this->check_out_date && $this->check_out_time )
            return $this->check_out_date->setTimeFromTimeString( $this->check_out_time )
                ->shiftTimezone($this->itinerary->user->getTimezone());

        return $this->check_out_date->shiftTimezone($this->itinerary->user->getTimezone());
    }

    public function getCheckOutDatetimeAttribute()
    {
        return $this->getCheckOutDatetimeLocaleAttribute()->fromUserPreferredTimezoneToAppTimezone($this->itinerary->user);
    }

    public function hotel_passengers()
    {
        return $this->hasMany(HotelPassenger::class);
    }

    public function hotel_rooms()
    {
        return $this->hasMany(HotelRoom::class);
    }

    public function hotel_amenities()
    {
        return $this->hasMany(HotelAmenity::class);
    }

    public function hotel_pictures()
    {
        return $this->hasMany(HotelPicture::class);
    }

    public function hotel_supplier()
    {
        return $this->hasOne(HotelSupplier::class);
    }


    /**
     * Get traceable folder path
     */
    public function getFolderStorageRelativePath(): string
    {
        return $this->getFolderStorageRelativePathUsingSector( 'hotels' );
    }

    /**
     * @inheritDoc
     */
    public function getSupplierRelationshipAttributeName(): string
    {
        return 'hotel_supplier';
    }

    /**
     * @inheritDoc
     */
    public function formatForSharing(): array
    {
        $supplier = $this->getSupplierAttribute();

        return [
            'title' => $this->title(),
            'address' => $supplier->getAddress(),
            'phone' => $supplier->getPhone(),

            'pictures' => $supplier->getPictures()->map->presentForDev(),
            "supplier" => $supplier->presentForDev(),

            'check_in_date' => $this->check_in_date->toDateString(  ),
            'check_out_date' => $this->check_out_date->toDateString(  ),

            "check_in_time" => $this->check_in_time,
            "check_out_time" => $this->check_out_time,

            "check_in_datetime_locale" => $this->getCheckInDatetimeLocaleAttribute()->toIso8601String(),
            "check_out_datetime_locale" => $this->getCheckOutDatetimeLocaleAttribute()->toIso8601String(),
            "check_in_datetime" => $this->getCheckInDatetimeAttribute()->toIso8601String(),
            "check_out_datetime" => $this->getCheckOutDatetimeAttribute()->toIso8601String(),

            "persons" => $this->hotel_passengers->count(),

            "amenities" => $this->hotel_amenities->pluck('amenity'),


            'rooms' => $this->hotel_rooms->map->presentForDev(),

            'price' => $this->getSum(),

            'notes' => $this->notes(),

            "id" => $this->id,
            "sorting_rank" => $this->sorting_rank,

            "confirmation_reference" => $this->confirmation_reference,
            "itinerary_id" => $this->itinerary_id,
            "cancel_policy" => $this->cancel_policy,
            "payment" => $this->payment,

            "booking_category_id" => $this->booking_category_id,
            "custom_header_title" => $this->custom_header_title,
            "save_to_library" => $this->getSavedToLibrary(),
        ];
    }

    /**
     * Must be locale
     * @inheritDoc
     */
    public function sortByKey(): Carbon
    {
        // No time manipulation here for now
        return  $this->getCheckInDatetimeLocaleAttribute() ;
    }

    /**
     * @inheritDoc
     */
    public function moveStartDate(Carbon $newDateLocale ): IShiftableBookingInterface
    {
        $daysDifference =  $this->check_in_date->diffInDays( $this->check_out_date );

        // No Time reference here for now
        $this->check_in_date = $newDateLocale->clone();
        $this->check_out_date = $this->check_in_date->addDays( $daysDifference );

        return $this;
    }


    /**
     * @inheritDoc
     */
    public function createCalendarEvent(bool $updateQuietly = true)
    {
        // TODO: Check adding time and make sure it is in SET time to UTC
        //
        return $this->createCalendarEventDayForBooking( $this->check_in_date, $this->check_out_date, $updateQuietly );
    }

    public function calendarEndDate(): Carbon
    {
        return $this->getCheckOutDatetimeLocaleAttribute() ?? $this->sortByKey();
    }
}
