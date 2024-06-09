<?php

namespace App\ModelsExtended;

use App\ModelsExtended\Interfaces\IBookingModelInterface;
use App\ModelsExtended\Interfaces\ICanCreateGoogleCalendarEventInterface;
use App\ModelsExtended\Interfaces\ICanCreateServiceProviderInterface;
use App\ModelsExtended\Interfaces\IReplicableEloquent;
use App\ModelsExtended\Interfaces\IShiftableBookingInterface;
use App\ModelsExtended\Traits\BelongsToItineraryTrait;
use App\ModelsExtended\Traits\CanCreateGoogleCalendarEventTrait;
use App\ModelsExtended\Traits\ItineraryBookingsSummableTrait;
use App\ModelsExtended\Traits\ReplicableEloquentTrait;
use App\ModelsExtended\Traits\ShareableSortablePackagerTrait;
use Carbon\Carbon;

class ItineraryInsurance extends \App\Models\ItineraryInsurance
    implements IBookingModelInterface, ICanCreateServiceProviderInterface, ICanCreateGoogleCalendarEventInterface
{
    use BelongsToItineraryTrait, ItineraryBookingsSummableTrait, ShareableSortablePackagerTrait, CanCreateGoogleCalendarEventTrait;

    public $doNotReplicateProperties = [
        'google_calendar_event_id'
    ];

    public $replicableRelations = [
      'insurance_pictures',
      'insurance_supplier'
    ];

    public function insurance_pictures()
    {
        return $this->hasMany(InsurancePicture::class);
    }

    public function insurance_supplier()
    {
        return $this->hasOne(InsuranceSupplier::class);
    }

    /**
     * Get traceable folder path
     */
    public function getFolderStorageRelativePath(): string
    {
        return $this->getFolderStorageRelativePathUsingSector( 'insurances' );
    }

    /**
     * @inheritDoc
     */
    public function getSupplierRelationshipAttributeName(): string
    {
        return 'insurance_supplier';
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

            'company' => $this->company,
            'confirmation_reference' => $this->confirmation_reference,
            'payment' => $this->payment,
            'policy_type' => $this->policy_type,
            'effective_date' => $this->effective_date->toDateString(),

            'price' => $this->getSum(),

            'notes' => $this->notes(),
            'cancel_policy' => $this->cancel_policy,

        ];
    }

    /**
     * @inheritDoc
     */
    public function sortByKey(): Carbon
    {
        return $this->effective_date?? $this->itinerary->start_date;
    }

    /**
     * @inheritDoc
     */
    public function moveStartDate(Carbon $newDateLocale ): IShiftableBookingInterface
    {
        // No Time reference here for now
        $this->effective_date = $newDateLocale->clone();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function createCalendarEvent(bool $updateQuietly = true)
    {
        // TODO: make sure it is time in UTC
        if(!$this->effective_date) return $this;
        return $this->createCalendarEventDayForBooking( $this->effective_date, $this->effective_date, $updateQuietly );
    }
}
