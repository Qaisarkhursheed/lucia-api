<?php

namespace App\ModelsExtended;

use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\IInteractsWithServiceSupplier;
use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\ISupplierCompatible;
use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\SupplierCompatibleTrait;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class TransportSupplier extends \App\Models\TransportSupplier implements ISupplierCompatible, IInteractsWithServiceSupplier
{
    use SupplierCompatibleTrait;

    public function itinerary_transport()
    {
        return $this->belongsTo(ItineraryTransport::class);
    }

    public function getPictures(): Collection
    {
        return $this->itinerary_transport->transport_pictures;
    }

    public function addPicture(UploadedFile $image)
    {
        // this is storing full url
        $image_relative_url = TransportPicture::generateImageRelativePath( $image  , $this->itinerary_transport );
        Storage::cloud()->put( $image_relative_url, $image->getContent()  );

        return $this->itinerary_transport->transport_pictures()->create([
            "image_url" => Storage::cloud()->url( $image_relative_url )
        ]);
    }

    /**
     * @return HasOne|null|ServiceSupplier
     */
    public function service_supplier(): ?HasOne
    {
        return $this->getLinkedServiceSupplier(BookingCategory::Transportation);
    }
}
