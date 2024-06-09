<?php

namespace App\ModelsExtended\Interfaces;

Interface IShareableRenderInterface
{
    /**
     * @return array
     */
    public function formatForSharing(): array;
}
