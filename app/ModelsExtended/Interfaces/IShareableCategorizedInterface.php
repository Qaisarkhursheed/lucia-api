<?php

namespace App\ModelsExtended\Interfaces;

interface IShareableCategorizedInterface extends IShareableRenderInterface,  IShareableSortableInterface
{
    /**
     * @return string
     */
    public function categorizeShareableAs(): string;
}

