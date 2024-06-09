<?php

namespace App\ModelsExtended\Interfaces;

use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\ISupplierCompatible;

interface ICanCreateServiceProviderInterface extends IDeveloperPresentationInterface
{
    /**
     * Get Relationship link name you used in the model
     * like concierge_supplier
     *
     * @return string
     */
    public function getSupplierRelationshipAttributeName():string;

    /**
     * Get booking category
     * @return int
     */
    public function getBookingCategoryId():int;

    /**
     * @return ISupplierCompatible|null
     */
    public function getSupplierAttribute():?ISupplierCompatible;

    /**
     * Indicate if we are saving to library
     * @return bool
     */
    public function getSavedToLibrary(): bool;

}
