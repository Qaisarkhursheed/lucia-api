<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings\Flights;

use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Agent\Itinerary\Bookings\FlightItemsController;
use App\Http\Controllers\Agent\Itinerary\IBookingCanStoreImageControllerInterface;
use App\ModelsExtended\FlightPicture;
use App\ModelsExtended\ModelBase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PictureController extends FlightItemsController implements IBookingCanStoreImageControllerInterface
{
    protected int $MAXIMUM_PICTURES = 3;

    public function __construct()
    {
        parent::__construct( "flight_picture_id" );
    }

    /**
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function fetchAll()
    {
        $query = FlightPicture::query()
            ->whereHas( "itinerary_flight.itinerary" , function ( Builder $builder ) {
                $builder->where( "itinerary.user_id", auth()->id() );
            })
            ->where( "itinerary_flight_id", $this->getFlightId() );

        return $query->get();
    }

    /**
     * @inheritDoc
     */
    public function store( Request $request )
    {
        return $this->invokeImageStoreRequest( $request, $this, $this->getFlight(), 'flight_pictures');
    }

    /**
     * @inheritDoc
     */
    public function storeImage(UploadedFile $image, ModelBase $baseModel)
    {
        $image_url =  FlightPicture::generateImageRelativePath( $image,  $baseModel  );

        Storage::cloud()->put( $image_url, $image->getContent() );

        return FlightPicture::create(
            [
                'itinerary_flight_id' => $this->getFlightId(),
                'image_url' => Storage::cloud()->url( $image_url )
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function delete()
    {
        Storage::cloud()->delete( $this->model->getImageUrlStorageRelativePath() );
        return parent::delete();
    }

    /**
     * @inheritDoc
     */
    public function loadModel( $route_param_value, bool $withRelations = true )
    {
        $query = FlightPicture::query()
            ->whereHas( "itinerary_flight.itinerary" , function ( Builder $builder ) {
                $builder->where( "itinerary.user_id", auth()->id() );
            })
            ->where( "itinerary_flight_id", $this->getFlightId() )
            ->where("id", $route_param_value);

        if(  $withRelations ) $query->with( 'itinerary_flight' );

        $this->model = $query->first();

        if( ! $this->model ) throw new RecordNotFoundException();

        return $this->model;
    }

    /**
     * @inheritDoc
     */
    public function getDataQuery(): Builder
    {
        return FlightPicture::query();
    }
}
