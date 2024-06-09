<?php

namespace App\ModelsExtended;

use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\ISupplierPictureCompatible;
use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\SupplierPictureCompatibleTrait;
use App\ModelsExtended\Traits\HasImageUrlFullPathSavingTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TransportPicture extends \App\Models\TransportPicture implements ISupplierPictureCompatible
{
    use HasImageUrlFullPathSavingTrait, SupplierPictureCompatibleTrait;

    public function itinerary_transport()
    {
        return $this->belongsTo(ItineraryTransport::class);
    }

    public function onSavedReplication(): Model
    {
        if( $this->image_url )
        {
            $old_filename = $this->image_url;
            $this->image_url = Storage::cloud()->url( self::generateImageRelativePathWithFileName( pathinfo( $this->image_url, PATHINFO_BASENAME ) , $this->itinerary_transport ) );

            // Copy file to location
            if( Storage::cloud()->exists( self::getStorageRelativePath( $old_filename ) ) )
            Storage::cloud()->copy( self::getStorageRelativePath( $old_filename ), self::getStorageRelativePath( $this->image_url )   );

            $this->updateQuietly();
        }
        return $this;
    }
}
