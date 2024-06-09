<?php

namespace App\ModelsExtended;

use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\IInteractsWithServiceSupplier;
use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\ISupplierCompatible;
use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\ISupplierPictureCompatible;
use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\SupplierCompatibleTrait;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Reliese\Database\Eloquent\Model;

class ConciergeSupplier extends \App\Models\ConciergeSupplier implements ISupplierCompatible, IInteractsWithServiceSupplier
{
    use SupplierCompatibleTrait;

    public function itinerary_concierge()
    {
        return $this->belongsTo(ItineraryConcierge::class);
    }

    /**
     * @return HasOne|null|ServiceSupplier
     */
    public function service_supplier(): ?HasOne
    {
        return $this->getLinkedServiceSupplier(BookingCategory::Concierge);
    }

    public function getPictures(): Collection
    {
        return $this->itinerary_concierge->concierge_pictures;
    }

    public function addPicture(UploadedFile $image)
    {
        // this is storing full url
        $image_relative_url = ConciergePicture::generateImageRelativePath( $image  , $this->itinerary_concierge );
        Storage::cloud()->put( $image_relative_url, $image->getContent()  );

        return $this->itinerary_concierge->concierge_pictures()->create([
            "image_url" => Storage::cloud()->url( $image_relative_url )
        ]);
    }
}
