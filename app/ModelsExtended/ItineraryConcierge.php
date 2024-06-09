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

/**
 * @property ConciergeSupplier $concierge_supplier
 */
class ItineraryConcierge extends \App\Models\ItineraryConcierge
    implements IBookingModelInterface, ICanCreateServiceProviderInterface, ICanCreateGoogleCalendarEventInterface
{
    use BelongsToItineraryTrait, ItineraryBookingsSummableTrait, ShareableSortablePackagerTrait, CanCreateGoogleCalendarEventTrait;

    protected $appends = [ 'start_datetime_locale', 'end_datetime_locale' ];

    public $replicableRelations = [
        'concierge_pictures',
        'concierge_supplier',
    ];

    public function concierge_pictures()
    {
        return $this->hasMany(ConciergePicture::class);
    }

    public function concierge_supplier()
    {
        return $this->hasOne(ConciergeSupplier::class);
    }

    /**
     * Get traceable folder path
     */
    public function getFolderStorageRelativePath(): string
    {
        return $this->getFolderStorageRelativePathUsingSector( 'concierges' );
    }

    public function getStartDatetimeLocaleAttribute()
    {
        return $this->start_datetime->fromAppTimezoneToPreferredTimezone();
    }

    public function getEndDatetimeLocaleAttribute()
    {
        return $this->end_datetime? $this->end_datetime->fromAppTimezoneToPreferredTimezone() : null;
    }

    /**
     * @inheritDoc
     */
    public function getSupplierRelationshipAttributeName(): string
    {
        return 'concierge_supplier';
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

            'price' => $this->getSum(),

            'start_datetime' => $this->displayDayDateFormatUTC( $this->start_datetime ),
            'end_datetime' => $this->displayDayDateFormatUTC( $this->end_datetime ),

            'start_datetime_locale' => $this->getDateTimeLocale( $this->start_datetime ),
            'end_datetime_locale' => $this->getDateTimeLocale( $this->end_datetime ),

            'cancel_policy' => $this->cancel_policy,
            'notes' => $this->notes(),

            'itinerary_id' => $this->itinerary_id,
            'payment' => $this->payment,
            'confirmation_reference' => $this->confirmation_reference,
            'confirmed_for' => $this->confirmed_for,
            'confirmed_by' => $this->confirmed_by,
            'service_type' => $this->service_type,
        ];
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
        $daysDifference =  $this->end_datetime? $this->start_datetime->diffInDays( $this->end_datetime ) : 0;

        $this->start_datetime = $newDateLocale->clone()->setTimeFrom( $this->getStartDatetimeLocaleAttribute() )->fromPreferredTimezoneToAppTimezone();

        if( $this->end_datetime )
        $this->end_datetime = $this->start_datetime->addDays( $daysDifference );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function createCalendarEvent(bool $updateQuietly = true)
    {
        return $this->createCalendarEventReminderTimeForBooking(
            $this->start_datetime,
            $this->end_datetime?? $this->start_datetime,
            $updateQuietly
        );
    }

    public function calendarEndDate(): Carbon
    {
        return $this->getEndDatetimeLocaleAttribute() ?? $this->sortByKey();
    }
}
