<?php

namespace App\ModelsExtended;

use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\IInteractsWithServiceSupplier;
use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\ISupplierCompatible;
use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\SupplierCompatibleTrait;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class InsuranceSupplier extends \App\Models\InsuranceSupplier implements ISupplierCompatible, IInteractsWithServiceSupplier
{
    use SupplierCompatibleTrait;

    public function itinerary_insurance()
    {
        return $this->belongsTo(ItineraryInsurance::class);
    }

    public function getPictures(): Collection
    {
        return $this->itinerary_insurance->insurance_pictures;
    }

    /**
     * @return HasOne|null|ServiceSupplier
     */
    public function service_supplier(): ?HasOne
    {
        return $this->getLinkedServiceSupplier(BookingCategory::Insurance);
    }

    public function addPicture(UploadedFile $image)
    {
        // this is storing full url
        $image_relative_url = InsurancePicture::generateImageRelativePath( $image  , $this->itinerary_insurance );
        Storage::cloud()->put( $image_relative_url, $image->getContent()  );

        return $this->itinerary_insurance->insurance_pictures()->create([
            "image_url" => Storage::cloud()->url( $image_relative_url )
        ]);
    }
}
