<?php

namespace App\Http\Controllers\Agent\Suppliers;

use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\ISupplierCompatible;
use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\ISupplierPictureCompatible;
use App\Http\Controllers\Enhancers\CRUDEnabledController;
use App\Http\Responses\OkResponse;
use App\ModelsExtended\ServiceSupplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * @property ISupplierPictureCompatible $model
 */
class SupplierPictureController extends CRUDEnabledController
{
    protected int $MAXIMUM_PICTURES = 6;

    public function __construct()
    {
        parent::__construct( "supplier_picture_id" );
    }

    /**
     * @return int|object|string|null
     */
    protected function getSupplierId()
    {
        return \request()->route( 'supplier_id' );
    }

    /**
     * @return ServiceSupplier|ISupplierCompatible
     */
    protected function getSupplier()
    {
        return ServiceSupplier::find( $this->getSupplierId() );
    }

    /**
     * @inheritDoc
     */
    public function delete()
    {
        $this->getSupplier()->deletePicture( $this->model->getId() );
        return new OkResponse();

    }
    public function getCommonRules()
    {
        return [
            'image_url' => 'required|array|max:' . $this->MAXIMUM_PICTURES,
            'image_url.*' => 'image|max:20000',    // 20MB
        ];
    }

    public function fetchAll()
    {
        return $this->getSupplier()->getPictures()->map->presentForDev();
    }

    /**
     * @inheritDoc
     */
    public function store( Request $request )
    {
        $this->validatedRules( $this->getCommonRules() );

        $supplierCompatible = $this->getSupplier();

        // find a way to centralize
        if( $supplierCompatible->getPictures()->count() === $this->MAXIMUM_PICTURES )
            throw new \Exception( "You have reached the maximum upload limit of " . $this->MAXIMUM_PICTURES );

        if( $supplierCompatible->getPictures()->count() + count( $request->file( 'image_url' ) ) > $this->MAXIMUM_PICTURES )
            throw new \Exception( "Your upload will go beyond the maximum upload limit of " . $this->MAXIMUM_PICTURES );

        foreach ($request->file('image_url') as $image) {
            $supplierCompatible->addPicture($image);
        }


//
//        $supplier_image = $request->file('image_url.0');
//
//        // delete if it exists
//        if( Storage::cloud()->exists(  $this->model->getImageUrlStorageRelativePath() ) )
//            Storage::cloud()->delete(  $this->model->getImageUrlStorageRelativePath() );
//
//        // We are generating new url to prevent caching
////            $this->model->image_url = $this->model->forceGetImageUrlPathOnCloud();
//        $this->model->image_url = ServiceSupplier::generateImageUrlFullPath();
//
//        // store the real full path
//        $this->storeImageUrl( $this->model->image_url, $supplier_image->getContent() );
//
//        $this->model->update();


        return $this->fetchAll();
    }

    /**
     * @inheritDoc
     */
    public function loadModel( $route_param_value, bool $withRelations = true )
    {
        $this->model = $this->getSupplier()->getPicture($route_param_value);
        return $this->model;
    }


    public function getDataQuery(): Builder
    {
        // TODO: Implement getDataQuery() method.
    }
}
