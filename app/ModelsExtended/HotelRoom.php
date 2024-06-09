<?php

namespace App\ModelsExtended;

use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;
use App\ModelsExtended\Traits\HasImageUrlFullPathSavingTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @property ItineraryHotel $itinerary_hotel
 */
class HotelRoom extends \App\Models\HotelRoom implements IDeveloperPresentationInterface
{
    use HasImageUrlFullPathSavingTrait;

    protected $appends = [ 'image_url'  ];

    public function itinerary_hotel()
    {
        return $this->belongsTo(ItineraryHotel::class);
    }

    /**
     * @return ?string
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->relative_image_url? Storage::cloud()->url( $this->relative_image_url ) : $this->relative_image_url;
    }

    /**
     * Delete only on cloud, it doesn't update model in database
     * Updates model locally.
     *
     * @return $this
     */
    public function deleteImageIfExists(): HotelRoom
    {
        if( $this->relative_image_url ){
            Storage::cloud()->delete( $this->relative_image_url );
            $this->relative_image_url = null;
        }
        return $this;
    }

    /**
     * Get traceable file name
     * @param UploadedFile $file
     * @param ItineraryHotel | ModelBase $hotel
     * @return string
     */
    public static function generateImageRelativePath(UploadedFile $file, ItineraryHotel $hotel): string
    {
        return HasImageUrlFullPathSavingTrait::generateImageRelativePath($file, $hotel, "room-images" );
    }

    public function onSavedReplication(): Model
    {
        if( $this->relative_image_url )
        {
            $old_filename = $this->relative_image_url;
            $this->relative_image_url = self::generateImageRelativePathWithFileName( pathinfo( $this->relative_image_url, PATHINFO_BASENAME ) , $this->itinerary_hotel , "room-images" );

            // Copy file to location
            if( Storage::cloud()->exists(  $old_filename ) )
            Storage::cloud()->copy( $old_filename, $this->relative_image_url   );

            $this->updateQuietly();
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function presentForDev(): array
    {
        return [
            "id" => $this->id,
            "room_type" => $this->room_type,
            "guest_name" => $this->guest_name,
            "room_rate" => $this->room_rate,
            "room_description" => $this->room_description,
            "number_of_guests" => $this->number_of_guests,
            "bedding_type" => $this->bedding_type,
            "currency_id" => $this->currency_id,
            "currency" => $this->currency_type->description,
            "image_url" => $this->getImageUrlAttribute(),
        ];
    }
}
