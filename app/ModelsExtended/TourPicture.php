<?php

namespace App\ModelsExtended;

use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\ISupplierPictureCompatible;
use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\SupplierPictureCompatibleTrait;
use App\ModelsExtended\Traits\HasImageUrlFullPathSavingTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TourPicture extends \App\Models\TourPicture implements ISupplierPictureCompatible
{
    use HasImageUrlFullPathSavingTrait, SupplierPictureCompatibleTrait;

    public function itinerary_tour()
    {
        return $this->belongsTo(ItineraryTour::class);
    }

    /**
     * Get traceable file name
     * @param UploadedFile $file
     * @param ItineraryTour|ModelBase $tour
     * @return string
     */
    public static function generateImageRelativePath(UploadedFile $file, ItineraryTour $tour )
    {
        return sprintf(
            "%s/pictures/%s-%s.%s",
            $tour->getFolderStorageRelativePath(),
            Str::slug( pathinfo( $file->getClientOriginalName(), PATHINFO_FILENAME ) ),
            Carbon::now()->timestamp,
            pathinfo( $file->getClientOriginalName(), PATHINFO_EXTENSION )
        );
    }

    public function onSavedReplication(): Model
    {
        if( $this->image_url )
        {
            $old_filename = $this->image_url;
            $this->image_url = Storage::cloud()->url( self::generateImageRelativePathWithFileName( pathinfo( $this->image_url, PATHINFO_BASENAME ) , $this->itinerary_tour ) );

            // Copy file to location
            if( Storage::cloud()->exists( self::getStorageRelativePath( $old_filename ) ) )
            Storage::cloud()->copy( self::getStorageRelativePath( $old_filename ), self::getStorageRelativePath( $this->image_url )   );

            $this->updateQuietly();
        }
        return $this;
    }
}
