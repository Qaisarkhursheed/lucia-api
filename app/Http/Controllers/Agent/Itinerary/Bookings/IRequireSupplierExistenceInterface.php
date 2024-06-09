<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings;

use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\IInteractsWithServiceSupplier;
use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\ISupplierCompatible;

interface IRequireSupplierExistenceInterface
{
    /**
     * @return IInteractsWithServiceSupplier|ISupplierCompatible
     * @throws \Exception
     */
    public function confirmHasSupplier();
}
