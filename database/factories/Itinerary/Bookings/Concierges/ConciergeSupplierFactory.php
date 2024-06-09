<?php

namespace Database\Factories\Itinerary\Bookings\Concierges;

use App\Models\ConciergeSupplier;
use Database\Factories\Itinerary\Bookings\Hotels\HotelSupplierFactory;

class ConciergeSupplierFactory extends HotelSupplierFactory
{
    protected $model = ConciergeSupplier::class;
}
