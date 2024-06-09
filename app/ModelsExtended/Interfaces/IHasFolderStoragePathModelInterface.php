<?php

namespace App\ModelsExtended\Interfaces;

/**
 * Indicate this class has a folder path it is managing
 */
interface IHasFolderStoragePathModelInterface
{
    public function getFolderStorageRelativePath(): string;
}
