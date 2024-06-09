<?php

namespace App\ModelsExtended;

use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\IInteractsWithServiceSupplier;
use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\ISupplierCompatible;
use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\SupplierCompatibleTrait;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class TourSupplier extends \App\Models\TourSupplier implements ISupplierCompatible, IInteractsWithServiceSupplier
{
    use SupplierCompatibleTrait;

    public function itinerary_tour()
    {
        return $this->belongsTo(ItineraryTour::class);
    }

    public function getPictures(): Collection
    {
        return $this->itinerary_tour->tour_pictures;
    }

    public function addPicture(UploadedFile $image)
    {
        // this is storing full url
        $image_relative_url = TourPicture::generateImageRelativePath( $image  , $this->itinerary_tour );
        Storage::cloud()->put( $image_relative_url, $image->getContent()  );

        return $this->itinerary_tour->tour_pictures()->create([
            "image_url" => Storage::cloud()->url( $image_relative_url )
        ]);
    }

    /**
     * @return HasOne|null|ServiceSupplier
     */
    public function service_supplier(): ?HasOne
    {
        return $this->getLinkedServiceSupplier(BookingCategory::Tour_Activity);
    }
}
