<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier;

use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;

interface ISupplierPictureCompatible extends IDeveloperPresentationInterface
{
   public function getId():int;
   public function getImageUrl():string;
   public function getRelativeImageUrl():string;

    /**
     * From Model
     * @return mixed
     */
   public function delete();
}
