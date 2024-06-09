<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings;

use App\Http\Controllers\Agent\Itinerary\Bookings\Hotels\AmenitiesController;
use App\Http\Controllers\Agent\Itinerary\Bookings\Hotels\RoomsController;
use App\Http\Controllers\Agent\Itinerary\ItineraryItemsController;
use App\Http\Middleware\ConvertStringsToBooleanMiddleware;
use App\Http\Responses\OkResponse;
use App\ModelsExtended\BookingCategory;
use App\ModelsExtended\ItineraryHotel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * @property ItineraryHotel $model
 */
class HotelController extends ItineraryItemsController
{
    public function __construct()
    {
        $this->middleware( ConvertStringsToBooleanMiddleware::class );

        parent::__construct( "hotel_id" );
    }

    public function getDataQuery(): Builder
    {
        return  $this->limitBookingToItineraryOwner(  ItineraryHotel::with(
            "hotel_amenities","hotel_pictures","hotel_supplier", "hotel_rooms"
            //            ,"hotel_passengers", "hotel_passengers.itinerary_passenger"
            ))
            ->where( "itinerary_id", $this->getItineraryId() );
    }

    public function getCommonRules()
    {
        return [
            'price' => 'filled|numeric',
            'check_in_date' => 'required|date_format:Y-m-d',
            'check_out_date' => 'required|date_format:Y-m-d|after_or_equal:check_in_date',
//            'travelers' => 'nullable|numeric',

            'check_in_time' => 'nullable|date_format:h\:i\ A',
            'check_out_time' => 'nullable|date_format:h\:i\ A',

            'confirmation_reference' => 'nullable|string|max:150',
            'payment' => 'nullable|string|max:150',

            'notes' => 'nullable|string|max:3000',
            'cancel_policy' => 'nullable|string|max:3000',
            'custom_header_title' => 'nullable|string|max:250',

        ];
    }

    /**
     * @inheritDoc
     */
    public function store( Request $request )
    {
        $this->validatedRules( array_merge( $this->getCommonRulesWithProvider(), [
            'amenities' => 'nullable|array|max:100',
            'rooms' => 'nullable|array|max:100',
        ]));

        return DB::transaction(function () use ($request) {
            $this->addNewHotel( $request )

            ->createProvider( $request )    // Linked and/or global are created here
//            ->createPassengers( $request )
            ->createAmenities( $request )
            ->createRooms( $request );

            $this->loadModel( $this->model->id );

            return $this->fetch();
        } );
    }

    /**
     * @param Request $request
     * @return HotelController
     */
    private function addNewHotel(Request $request )
    {
        $this->model = $this->getItinerary()->itinerary_hotels()->create(
            array_merge([
                'booking_category_id' => BookingCategory::Hotel,
                'sorting_rank' => $this->getItinerary()->getNextSortingRankFor( Carbon::createFromFormat('Y-m-d',$request->input('check_in_date')  ) ),
            ],
            $request->only(
                [
                    'price',
                    'check_in_date',
                    'check_out_date',
                    'check_in_time',
                    'check_out_time',
//                    'travelers',
                    'confirmation_reference',
                    'cancel_policy',
                    'notes',
                    'payment',
                    'custom_header_title',
                ]
            )
            )
        );

        return $this;
    }

//    /**
//     * Create Passengers if correctly passed in as array
//     *
//     * @param Request $request
//     * @return $this
//     */
//    private function createPassengers(Request $request)
//    {
//        $passengers = $request->input('passengers' );
//        if( $passengers && count( $passengers ) ) {
//            ItineraryItemsController::validatePassengers($passengers);
//
//            foreach ($passengers as $passenger)
//                $this->model->hotel_passengers()->create(
//                    [
//                        "itinerary_passenger_id" => ItineraryPassenger::updateOrCreate($this->model->itinerary, $passenger)->id,
//                        "room" => Arr::get($passenger, "room")
//                    ]
//                );
//        }
//        return $this;
//    }

    /**
     * @param Request $request
     * @return $this
     */
    private function createAmenities(Request $request)
    {
        $amenities = $request->input('amenities' );
        if( $amenities && count( $amenities ) ) {
            $AmenitiesController = new AmenitiesController();
            foreach ($amenities as $amenity )
            {
                Validator::validate( [ 'amenity' => $amenity ] , $AmenitiesController->getCommonRules() );
                $this->model->hotel_amenities()->create([ 'amenity' => $amenity ]);
            }
        }
        return $this;
    }


    /**
     * @param Request $request
     * @return $this
     */
    private function createRooms(Request $request)
    {
        $rooms = $request->input('rooms' );
        if( $rooms && count( $rooms ) ) {
            $RoomsController = new RoomsController();
            foreach ($rooms as $index => $room )
            {
                Validator::validate( $room , $RoomsController->getCommonRules() );
                $hotel_room = $this->model->hotel_rooms()->create($room);
                $file_key = "rooms.$index.image_url";
                if( $request->hasFile( $file_key ) )
                {
                    $RoomsController->updateImageWithFile( $hotel_room, $request->file( $file_key ) );
                }
            }
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function update( Request $request )
    {
        $this->validatedRules($this->getCommonRulesWithProviderForUpdate());

        $this->model->update($request->only(
            [
                'price',
                'check_in_date',
                'check_out_date',
                'check_in_time',
                'check_out_time',
//                'travelers',
                'confirmation_reference',
                'cancel_policy',
                'notes',
                'payment',
                'custom_header_title',
            ]
        )
        );

//        Update here needs to detect which provider to update
//         Linked version or global
        return $this->updateProvider( $request )->fetch();
//        return $this->fetch();
    }

    /**
     * @inheritDoc
     */
    public function delete()
    {
        Storage::cloud()->deleteDirectory($this->model->getFolderStorageRelativePath());
        return parent::delete();
    }

    public function fetch()
    {
        return new OkResponse( $this->model->presentForDev() );
    }

    public function fetchAll()
    {
        return parent::fetchAll()->map->presentForDev();
    }

}
