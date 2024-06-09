<?php

namespace App\ModelsExtended;

use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;
use App\ModelsExtended\Traits\HasImageUrlFullPathSavingTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ItineraryDocument extends \App\Models\ItineraryDocument implements IDeveloperPresentationInterface
{
    use HasImageUrlFullPathSavingTrait;

    protected $appends = ['document_url'];

    /**
     * @return string
     */
    public function getDocumentUrlAttribute(): string
    {
        return Storage::cloud()->url($this->document_relative_url);
    }

    public function itinerary()
    {
        return $this->belongsTo(Itinerary::class);
    }

    /**
     * Get traceable file name
     * @param UploadedFile $file
     * @param Itinerary | ModelBase $itinerary
     * @return string
     */
    public static function generateRelativePath(UploadedFile $file, Itinerary $itinerary): string
    {
        return self::generateImageRelativePath($file, $itinerary, "documents");
    }

    public function onSavedReplication(): Model
    {
        if( $this->document_relative_url )
        {
            $old_filename = $this->document_relative_url;
            $this->document_relative_url = self::generateImageRelativePathWithFileName( pathinfo( $this->document_relative_url, PATHINFO_BASENAME ) , $this->itinerary  );

            // Copy file to location
            if( Storage::cloud()->exists(  $old_filename ) )
            Storage::cloud()->copy( $old_filename, $this->document_relative_url   );

            $this->updateQuietly();
        }
        return $this;
    }

    public function presentForDev(): array
    {
        return $this->only([ 'document_url', "name", "id" ]);
    }
}
