<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier;
use App\ModelsExtended\ModelBase;
use App\ModelsExtended\Traits\HasImageUrlDevPresentTrait;

/**
 * @property int $id
 * @property string $image_url
 * @property string $image_relative_url
 */
trait SupplierPictureCompatibleTrait
{
    use HasImageUrlDevPresentTrait;

    public function getId(): int
    {
        return $this->id;
    }

    public function getImageUrl(): string
    {
        return $this->image_url;
    }

    public function getRelativeImageUrl(): string
    {
        return ModelBase::getStorageRelativePath( $this->getImageUrl() );
    }

    public function presentForDev(): array
    {
       return [
           "id" => $this->getId(),
           "image_url" => $this->getImageUrl(),
       ];
    }
}
