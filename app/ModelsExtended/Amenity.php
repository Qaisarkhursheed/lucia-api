<?php

namespace App\ModelsExtended;

class Amenity extends \App\Models\Amenity
{
    protected $appends = [ 'image_url'  ];

    /**
     * @return ?string
     */
    public function getImageUrlAttribute(): ?string
    {
        if( $this->image_relative_url )
            return myAssetUrl( $this->image_relative_url );

        return  $this->image_relative_url;
    }
}
