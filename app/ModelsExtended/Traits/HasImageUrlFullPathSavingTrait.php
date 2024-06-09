<?php

namespace App\ModelsExtended\Traits;

use App\ModelsExtended\ModelBase;
use Illuminate\Http\UploadedFile;

trait HasImageUrlFullPathSavingTrait
{
    /**
     * Get traceable file name
     *
     * @param UploadedFile $file
     * @param ModelBase $modelBase
     * @param string $containerName
     * @return string
     */
    public static function generateImageRelativePath(UploadedFile $file, ModelBase $modelBase, string $containerName = "pictures" ): string
    {
        return  self::generateImageRelativePathWithFileName( $file->hashName(), $modelBase, $containerName );
    }

    /**
     * Get traceable file name
     *
     * @param string $fileName
     * @param ModelBase $modelBase
     * @param string $containerName
     * @return string
     */
    public static function generateImageRelativePathWithFileName(string $fileName, ModelBase $modelBase, string $containerName = "pictures"): string
    {
        return sprintf("%s/%s/%s",$modelBase->getFolderStorageRelativePath(), $containerName, $fileName);
    }
}
