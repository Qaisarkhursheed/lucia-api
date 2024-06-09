<?php

namespace App\ModelsExtended;

use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;
use App\ModelsExtended\Interfaces\IHasFolderStoragePathModelInterface;
use App\ModelsExtended\Traits\HasImageUrlSavingModelTrait;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * @property User $user
 */
class CopilotInfo extends \App\Models\CopilotInfo implements IDeveloperPresentationInterface
{
    use HasImageUrlSavingModelTrait;

    protected $appends = ['resume_url'];

    public function getResumeUrlAttribute(): ?string
    {
        return $this->resume_relative_url? Storage::cloud()->url($this->resume_relative_url) : null;
    }

    public static function generateImageRelativePath(UploadedFile $file, IHasFolderStoragePathModelInterface $modelBase, string $containerName = "pictures"): string
    {
        return  self::generateImageRelativePathWithFileName( $file->hashName(), $modelBase, "resume" );
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'copilot_id');
    }

    public function presentForDev(): array
    {
        return $this->toArray();
    }
}