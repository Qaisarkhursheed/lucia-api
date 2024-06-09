<?php

namespace App\ModelsExtended\Traits;

trait HasImageUrlDevPresentTrait
{
    /**
     * @inheritDoc
     */
    public function presentForDev(): array
    {
        return [
            "id" => $this->id,
            "image_url" => $this->image_url
        ];
    }
}
