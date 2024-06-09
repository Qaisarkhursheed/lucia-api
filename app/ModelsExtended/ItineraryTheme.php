<?php

namespace App\ModelsExtended;

use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @property Itinerary $itinerary
 */
class ItineraryTheme extends \App\Models\ItineraryTheme implements IDeveloperPresentationInterface
{
    /**
     * Get full url path for itinerary logo
     *
     * @param Itinerary|ModelBase $itinerary
     * @return string
     */
    public static function generateItineraryLogoUrlFullPath(Itinerary $itinerary): string
    {
        return Storage::cloud()->url(
            sprintf(
                "%s/itinerary-logo-%s.png",
                $itinerary->getFolderStorageRelativePath(),
                Str::random( )
            )
        );
    }

    /**
     * This one considers the theme set on profile as well.
     *
     * @return string|null
     */
    public function getLogoUrlAttribute(): ?string
    {
        return $this->itinerary_logo_url?? optional($this->itinerary->user->default_itinerary_theme)->itinerary_logo_url;
    }

    /**
     * @inheritDoc
     */
    public function presentForDev(): array
    {
        return [
            "id" => $this->id,
            'abstract_position_id' => $this->abstract_position_id,
            'property_position' => optional($this->property_position)->description,
            'hide_abstract' => $this->hide_abstract,

            "property_design_id" => $this->property_design_id,
            "property_design" => optional($this->property_design)->description,
            "itinerary_logo_url" => $this->getLogoUrlAttribute(),
        ];
    }

    public function itinerary()
    {
        return $this->belongsTo(Itinerary::class);
    }

    public function onSavedReplication(): Model
    {
        if( $this->itinerary_logo_url )
        {
            $old_filename = $this->itinerary_logo_url;
            $this->itinerary_logo_url = self::generateItineraryLogoUrlFullPath($this->itinerary );

            // Copy file to location
            if( Storage::cloud()->exists( self::getStorageRelativePath( $old_filename ) ) )
            Storage::cloud()->copy( self::getStorageRelativePath( $old_filename ), self::getStorageRelativePath( $this->itinerary_logo_url )   );

            $this->updateQuietly();
        }
        return $this;
    }
}
