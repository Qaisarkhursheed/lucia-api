<?php

namespace App\ModelsExtended;

use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * @property string $itinerary_logo_url
 * @property User $user
 */
class DefaultItineraryTheme extends \App\Models\DefaultItineraryTheme implements IDeveloperPresentationInterface
{
    protected $appends = [ 'itinerary_logo_url'  ];

    /**
     * @return ?string
     */
    public function getItineraryLogoUrlAttribute(): ?string
    {
        return $this->itinerary_logo_relative_url? Storage::cloud()->url( $this->itinerary_logo_relative_url ) : $this->itinerary_logo_relative_url;
    }

    /**
     * Get traceable file name
     *
     * @param UploadedFile $file
     * @param User $user
     * @return string
     */
    public function generateImageRelativePath(UploadedFile $file): string
    {
        return sprintf("%s/default-itinerary/%s", $this->user->getFolderStorageRelativePath(),$file->hashName() );
    }

    /**
     * This will store it in cloud and place the parameter value on the class
     * but it will persist it into database
     *
     * @param UploadedFile $file
     * @return DefaultItineraryTheme
     */
    public function storeNewItineraryLogo(UploadedFile $file): DefaultItineraryTheme
    {
        if( $this->itinerary_logo_relative_url && Storage::cloud()->exists( $this->itinerary_logo_relative_url ) )
            Storage::cloud()->delete( $this->itinerary_logo_relative_url);

        $this->itinerary_logo_relative_url = $this->generateImageRelativePath( $file );

        Storage::cloud()->put( $this->itinerary_logo_relative_url, $file->getContent() );

        return $this;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @inheritDoc
     */
    public function presentForDev(): array
    {
        return [
            "id" => $this->id,
            "property_design_id" => $this->property_design_id,
            "property_design" => optional($this->property_design)->description,
            "itinerary_logo_url" => $this->itinerary_logo_url,
        ];
    }
}
