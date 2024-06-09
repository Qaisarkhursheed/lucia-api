<?php

namespace App\ModelsExtended;

use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\IInteractsWithServiceSupplier;
use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\ISupplierCompatible;
use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\SupplierCompatibleTrait;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class CruiseSupplier extends \App\Models\CruiseSupplier implements ISupplierCompatible, IInteractsWithServiceSupplier
{
    use SupplierCompatibleTrait;

    public function itinerary_cruise()
    {
        return $this->belongsTo(ItineraryCruise::class);
    }

    public function getPictures(): Collection
    {
        return $this->itinerary_cruise->cruise_pictures;
    }

    /**
     * @return HasOne|null|ServiceSupplier
     */
    public function service_supplier(): ?HasOne
    {
        return $this->getLinkedServiceSupplier(BookingCategory::Cruise);
    }

    public function addPicture(UploadedFile $image)
    {
        // this is storing full url
        $image_relative_url = CruisePicture::generateImageRelativePath( $image  , $this->itinerary_cruise );
        Storage::cloud()->put( $image_relative_url, $image->getContent()  );

        return $this->itinerary_cruise->cruise_pictures()->create([
            "image_url" => Storage::cloud()->url( $image_relative_url )
        ]);
    }
}
