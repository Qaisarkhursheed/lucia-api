<?php

namespace Database\Factories\Itinerary\Bookings\Cruises;

use App\Models\CruiseSupplier;
use Database\Factories\Itinerary\Bookings\Hotels\HotelSupplierFactory;

class CruiseSupplierFactory extends HotelSupplierFactory
{
    protected $model = CruiseSupplier::class;
}
