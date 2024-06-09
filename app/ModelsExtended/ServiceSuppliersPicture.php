<?php

namespace App\ModelsExtended;

use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\ISupplierPictureCompatible;
use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\SupplierPictureCompatibleTrait;
use App\ModelsExtended\Interfaces\IHasImageUrlInterface;
use App\ModelsExtended\Traits\HasImageUrlSavingModelTrait;

/**
 * @property ServiceSupplier $service_supplier
 * @property ?string $image_url
 */
class ServiceSuppliersPicture extends \App\Models\ServiceSuppliersPicture implements IHasImageUrlInterface, ISupplierPictureCompatible
{
    protected $appends = [ 'image_url'  ];

    use HasImageUrlSavingModelTrait, SupplierPictureCompatibleTrait;

    /**
     * This is just to support legacy approach
     *
     * @return string|null
     */
    public function getImageUrlStorageRelativePath(): ?string
    {
        return $this->image_relative_url;
    }

    public function service_supplier()
    {
        return $this->belongsTo(ServiceSupplier::class, 'service_suppliers_id');
    }

}
