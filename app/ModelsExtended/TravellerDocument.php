<?php

namespace App\ModelsExtended;

use App\ModelsExtended\Traits\HasImageUrlFullPathSavingTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * @property Traveller $traveller
 */
class TravellerDocument extends \App\Models\TravellerDocument
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

    public function traveller()
    {
        return $this->belongsTo(Traveller::class);
    }

    /**
     * Get traceable file name
     * @param UploadedFile $file
     * @param Traveller $traveller
     * @return string
     */
    public static function generateRelativePath(UploadedFile $file, Traveller $traveller): string
    {
        return self::generateImageRelativePath($file, $traveller, "documents");
    }

    public function onSavedReplication(): Model
    {
        if( $this->document_relative_url )
        {
            $old_filename = $this->document_relative_url;
            $this->document_relative_url = self::generateImageRelativePathWithFileName( pathinfo( $this->document_relative_url, PATHINFO_BASENAME ) , $this->traveller  );

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
