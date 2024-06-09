<?php

namespace App\ModelsExtended;

use App\ModelsExtended\Traits\HasImageUrlFullPathSavingTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FlightPicture extends \App\Models\FlightPicture
{
    use HasImageUrlFullPathSavingTrait;

    public function itinerary_flight()
    {
        return $this->belongsTo(ItineraryFlight::class);
    }

    public function onSavedReplication(): Model
    {
        if( $this->image_url )
        {
            $old_filename = $this->image_url;
            $this->image_url = Storage::cloud()->url( self::generateImageRelativePathWithFileName( pathinfo( $this->image_url, PATHINFO_BASENAME ) , $this->itinerary_flight ) );

            // Copy file to location
            if( Storage::cloud()->exists( self::getStorageRelativePath( $old_filename ) ) )
            Storage::cloud()->copy( self::getStorageRelativePath( $old_filename ), self::getStorageRelativePath( $this->image_url )   );

            $this->updateQuietly();
        }
        return $this;
    }
}
