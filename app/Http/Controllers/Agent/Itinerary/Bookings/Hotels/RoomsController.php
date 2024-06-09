<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings\Hotels;

use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Agent\Itinerary\Bookings\HotelItemsController;
use App\Http\Middleware\ConvertEmptyStringToNullMiddleware;
use App\Http\Responses\OkResponse;
use App\ModelsExtended\HotelRoom;
use App\ModelsExtended\Interfaces\ICanCreateServiceProviderInterface;
use App\ModelsExtended\ModelBase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RoomsController extends HotelItemsController
{
    public function __construct()
    {
        parent::__construct( "hotel_room_id" );
        $this->middleware( ConvertEmptyStringToNullMiddleware::class );
    }

    /**
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function fetchAll()
    {
        $query = HotelRoom::query()
            ->whereHas( "itinerary_hotel.itinerary" , function ( Builder $builder ) {
                $builder->where( "itinerary.user_id", auth()->id() );
            })
            ->where( "itinerary_hotel_id", $this->getHotelId() );

        return $query->get();
    }

    public function getCommonRules()
    {
        return [
            'room_type' => 'required|string|max:100',
            'guest_name' => 'nullable|string|max:100',
            'room_description' => 'nullable|string|max:10000',
            'bedding_type' => 'required|string|max:100',
            'currency_id' => 'filled|numeric|exists:currency_types,id',
            'image_url' => 'filled|image|max:20000',    // 20MB
            'number_of_guests' => 'filled|integer|min:0',
            'room_rate' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * @inheritDoc
     */
    public function store( Request $request )
    {

       return DB::transaction(function () use ( $request ){
           return $this->updateImageIfExists($request,  $this->getHotel()->hotel_rooms()
               ->create($this->validatedRules( $this->getCommonRules() ))
           )->withoutRelations();
       });
    }

    /**
     * @inheritDoc
     */
    public function update( Request $request )
    {
        $this->model->update( $this->validatedRules( $this->getCommonRules() ) );

        return $this->updateImageIfExists($request, $this->model)->withoutRelations();
    }

    /**
     * @inheritDoc
     */
    public function loadModel( $route_param_value, bool $withRelations = true )
    {
        $query =  HotelRoom::query()
            ->whereHas( "itinerary_hotel.itinerary" , function ( Builder $builder ) {
                $builder->where( "itinerary.user_id", auth()->id() );
            })
            ->where( "itinerary_hotel_id", $this->getHotelId() )
            ->where("id", $route_param_value);

        if(  $withRelations ) $query->with( "currency_type" );

        $this->model = $query->first();

        if( ! $this->model ) throw new RecordNotFoundException();

        return $this->model;
    }

    /**
     * @inheritDoc
     */
    public function getDataQuery(): Builder
    {
        return HotelRoom::query();
    }

    /**
     * @param Request $request
     * @param HotelRoom | Model $room
     * @return void
     */
    public function updateImageIfExists(Request $request, HotelRoom $room): HotelRoom
    {
        if ($request->hasFile('image_url')) {
            return $this->updateImageWithFile( $room, $request->file('image_url')  );
        }
        return $room;
    }


    /**
     * @param HotelRoom | Model $room
     * @param UploadedFile $file
     * @return void
     */
    public function updateImageWithFile(HotelRoom $room, UploadedFile $file): HotelRoom
    {
        $image_url = HotelRoom::generateImageRelativePath($file, $room->itinerary_hotel);
        Storage::cloud()->put($image_url, $file->getContent());
        $room->relative_image_url = $image_url;
        $room->updateQuietly();
        return $room;
    }

    /**
     * @inheritDoc
     */
    public function addImage( Request $request )
    {
        $this->validatedRules( Arr::only( $this->getCommonRules(), [ 'image_url' ] ) );

        return DB::transaction(function () use ( $request ){
            return $this->updateImageIfExists($request,  $this->model )->withoutRelations();
        });
    }

    /**
     * @return ModelBase|ICanCreateServiceProviderInterface
     * @throws RecordNotFoundException
     */
    public function deleteImage()
    {
        $this->model->deleteImageIfExists();
        $this->model->updateQuietly();

        return $this->model->withoutRelations();
    }

    /**
     * @return OkResponse
     * @throws RecordNotFoundException
     */
    public function delete(): OkResponse
    {
        $this->model->deleteImageIfExists();
        return parent::delete();
    }
}
