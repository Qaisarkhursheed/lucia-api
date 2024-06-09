<?php

namespace App\ModelsExtended;

use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AdvisorRequestAttachment extends \App\Models\AdvisorRequestAttachment implements IDeveloperPresentationInterface
{
    protected $appends = [ 'document_url'  ];

    public function advisor_request()
    {
        return $this->belongsTo(AdvisorRequest::class);
    }

    /**
     * @return string
     */
    public function getDocumentUrlAttribute(): string
    {
        return Storage::cloud()->url( $this->document_relative_url );
    }

    /**
     * Get traceable file name
     * @param UploadedFile $file
     * @param AdvisorRequest|ModelBase $advisorRequest
     * @return string
     */
    public static function generateRelativePath(UploadedFile $file, AdvisorRequest $advisorRequest): string
    {
        return sprintf( "%s/%s", $advisorRequest->getFolderStorageRelativePath(), $file->hashName() );
    }

    /**
     * @inheritDoc
     */
    public function presentForDev(): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "document_relative_url" => $this->document_relative_url,
            "document_url" => $this->getDocumentUrlAttribute(),
        ];
    }
}
