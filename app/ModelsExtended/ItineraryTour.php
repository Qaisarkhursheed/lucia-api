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

class ItineraryTour extends \App\Models\ItineraryTour
    implements ICanCreateServiceProviderInterface, IBookingModelInterface, ICanCreateGoogleCalendarEventInterface
{
    use BelongsToItineraryTrait, ItineraryBookingsSummableTrait, ShareableSortablePackagerTrait, CanCreateGoogleCalendarEventTrait;

    protected $appends = [ 'start_datetime_locale', 'end_datetime_locale' ];

    public $replicableRelations = [
        "tour_supplier",
        "tour_pictures",
    ];

    public function tour_pictures()
    {
        return $this->hasMany(TourPicture::class);
    }

    public function tour_supplier()
    {
        return $this->hasOne(TourSupplier::class);
    }

    /**
     * Get traceable folder path
     */
    public function getFolderStorageRelativePath(): string
    {
        return $this->getFolderStorageRelativePathUsingSector( 'tours' );
    }

    public function getStartDatetimeLocaleAttribute()
    {
        return $this->start_datetime->fromAppTimezoneToPreferredTimezone();
    }

    public function getEndDatetimeLocaleAttribute()
    {
        return $this->end_datetime->fromAppTimezoneToPreferredTimezone();
    }
    /**
     * @inheritDoc
     */
    public function getSupplierRelationshipAttributeName(): string
    {
        return 'tour_supplier';
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
            'address' => optional($supplier)->getAddress(),
            'phone' => optional($supplier)->getPhone(),

            'pictures' => presentCollectionForDev(optional($supplier)->getPictures()),
            "supplier" => optional($supplier)->presentForDev(),

            'meeting_point' => $this->meeting_point,
            'confirmation_reference' => $this->confirmation_reference,
            'payment' => $this->payment,
            'price' => $this->getSum(),

            'start_datetime' => $this->displayDayDateFormatUTC( $this->start_datetime ),
            'end_datetime' => $this->displayDayDateFormatUTC( $this->end_datetime ),

            'start_datetime_locale' => $this->getDateTimeLocale( $this->start_datetime ),
            'end_datetime_locale' => $this->getDateTimeLocale( $this->end_datetime ),

            'notes' => $this->notes(),

            'description' => $this->description,
        ];
    }

    /**
     * @inheritDoc
     */
    public function createCalendarEvent(bool $updateQuietly = true)
    {
        return $this->createCalendarEventReminderTimeForBooking( $this->start_datetime, $this->end_datetime, $updateQuietly );
    }

    /**
     * @inheritDoc
     */
    public function sortByKey(): Carbon
    {
        return $this->getDateTimeLocale( $this->start_datetime );
    }

    /**
     * @inheritDoc
     */
    public function moveStartDate(Carbon $newDateLocale ): IShiftableBookingInterface
    {
        $daysDifference =  $this->start_datetime->diffInDays( $this->end_datetime );

        $this->start_datetime = $newDateLocale->clone()->setTimeFrom( $this->getStartDatetimeLocaleAttribute() )->fromPreferredTimezoneToAppTimezone();
        $this->end_datetime = $this->start_datetime->addDays( $daysDifference );

        return $this;
    }

    public function calendarEndDate(): Carbon
    {
        return $this->getEndDatetimeLocaleAttribute() ?? $this->sortByKey();
    }
}
