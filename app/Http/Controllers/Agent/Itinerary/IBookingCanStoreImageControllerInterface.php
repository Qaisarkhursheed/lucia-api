<?php

namespace App\Http\Controllers\Agent\Itinerary;

use App\ModelsExtended\ModelBase;
use Illuminate\Http\UploadedFile;

interface IBookingCanStoreImageControllerInterface
{
    /**
     * This calls the function to store the real image on relationship of the baseModel
     *
     * @param UploadedFile $image
     * @param ModelBase $baseModel
     * @return mixed
     */
    public function storeImage( UploadedFile $image, ModelBase $baseModel );
}
