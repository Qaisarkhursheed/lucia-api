<?php

namespace App\ModelsExtended;

use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;
use App\ModelsExtended\Interfaces\IHasImageUrlInterface;
use App\ModelsExtended\Traits\HasImageUrlDevPresentTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @property Itinerary $itinerary
 */
class ItineraryPicture extends \App\Models\ItineraryPicture implements IHasImageUrlInterface, IDeveloperPresentationInterface
{
    use HasImageUrlDevPresentTrait;

    public function itinerary()
    {
        return $this->belongsTo(Itinerary::class);
    }

    /**
     * Get traceable file name
     * @param UploadedFile $file
     * @param Itinerary $itinerary
     * @return string
     */
    public static function generateImageRelativePath(UploadedFile $file, Itinerary $itinerary ): string
    {
        return  self::generateImageRelativePathWithFileName( $file->hashName(), $itinerary );
    }

    /**
     * Get traceable file name
     *
     * @param string $fileName
     * @param Itinerary $itinerary
     * @return string
     */
    public static function generateImageRelativePathWithFileName(string $fileName, Itinerary $itinerary ): string
    {
        return sprintf("%s/itinerary-pictures/%s",$itinerary->getFolderStorageRelativePath(),$fileName);
    }

    public function onSavedReplication(): Model
    {
        if( $this->image_url )
        {
            $old_filename = $this->image_url;
            $this->image_url = Storage::cloud()->url( self::generateImageRelativePathWithFileName( pathinfo( $this->image_url, PATHINFO_BASENAME ) , $this->itinerary ) );

            // Copy file to location
            if( Storage::cloud()->exists( self::getStorageRelativePath( $old_filename ) ) )
            Storage::cloud()->copy( self::getStorageRelativePath( $old_filename ), self::getStorageRelativePath( $this->image_url )   );

            $this->updateQuietly();
        }
        return $this;
    }
}
