<?php

namespace App\ModelsExtended\Interfaces;

interface IHasImageUrlInterface
{
    public function getImageUrlStorageRelativePath(): ?string;
}
