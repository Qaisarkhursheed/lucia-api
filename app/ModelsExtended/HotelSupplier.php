<?php

namespace App\ModelsExtended;

use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\IInteractsWithServiceSupplier;
use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\ISupplierCompatible;
use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\SupplierCompatibleTrait;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * @property ItineraryHotel $itinerary_hotel
 * @property ServiceSupplier|null $service_supplier
 */
class HotelSupplier extends \App\Models\HotelSupplier implements ISupplierCompatible, IInteractsWithServiceSupplier
{
    use SupplierCompatibleTrait;

    public function itinerary_hotel()
    {
        return $this->belongsTo(ItineraryHotel::class);
    }

    /**
     * @return HasOne|null|ServiceSupplier
     */
    public function service_supplier(): ?HasOne
    {
        return $this->getLinkedServiceSupplier(BookingCategory::Hotel);
    }

    public function getPictures(): Collection
    {
       return $this->itinerary_hotel->hotel_pictures;
    }

    public function addPicture(UploadedFile $image)
    {
        // this is storing full url
        $image_relative_url = HotelPicture::generateImageRelativePath( $image  , $this->itinerary_hotel );
        Storage::cloud()->put( $image_relative_url, $image->getContent()  );

        return $this->itinerary_hotel->hotel_pictures()->create([
           "image_url" => Storage::cloud()->url( $image_relative_url )
       ]);
    }
}
