<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier;

use App\ModelsExtended\ServiceSupplier;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property bool $save_to_library
 */
interface IInteractsWithServiceSupplier
{
    /**
     * @return HasOne|null|ServiceSupplier
     */
    public function service_supplier(): ?HasOne;
}
