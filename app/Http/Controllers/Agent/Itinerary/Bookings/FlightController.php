<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings;

use App\Http\Controllers\Agent\Itinerary\Bookings\Flights\FlightSegmentController;
use App\Http\Controllers\Agent\Itinerary\ItineraryItemsController;
use App\Http\Responses\OkResponse;
use App\ModelsExtended\BookingCategory;
use App\ModelsExtended\ItineraryFlight;
use App\ModelsExtended\ItineraryPassenger;
use App\Repositories\IFlightSearchAPI;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * @property ItineraryFlight $model
 */
class FlightController extends ItineraryItemsController
{
    public function __construct()
    {
        parent::__construct( "flight_id" );
    }

    /**
     * @param IFlightSearchAPI $searchAPI
     * @return \App\Repositories\IFlightSearchResult
     * @throws \App\Exceptions\APIInvocationException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function searchFlightNumber( IFlightSearchAPI $searchAPI)
    {
        $this->validatedRules(
            [
                'flight_number' => [ 'required', 'regex:/^[a-zA-Z]{2}+[0-9]+/i', 'max:10' ],
                'departure_date' => 'required|date_format:Y-m-d',
            ]
        );

        return $searchAPI->search(
            Carbon::createFromFormat( 'Y-m-d' , \request( 'departure_date' ) ) ,
            \request( 'flight_number' )
        );
    }

    /**
     * @return Builder
     */
    public function getDataQuery(): Builder
    {
        return  $this->limitBookingToItineraryOwner( ItineraryFlight::with( "flight_passengers", "flight_pictures",
            "flight_passengers.itinerary_passenger", "itinerary_flight_segments" ))
            ->where( "itinerary_id", $this->getItineraryId() );
    }

    /**
     * @inheritDoc
     */
    public function getCommonRules()
    {
        return [
            'confirmation_number' => 'nullable|string',
//            'custom_header_title' => 'nullable|string|max:250',
            'check_in_url' => 'nullable|string|max:250',

            'notes' => 'nullable|string|max:3000',
            'cancel_policy' => 'nullable|string|max:3000',

            'price' => 'nullable|string|max:50',
        ];
    }

    /**
     * @inheritDoc
     */
    public function store( Request $request )
    {
        $this->validatedRules( array_merge( $this->getCommonRules(), [
            'passengers' => 'nullable|array|max:100',
            'segments' => 'required|array|max:100|min:1',
        ]));
        return DB::transaction(function () use ($request) {
           $this->addNew( $request )
                ->createSegments( $request )
                ->createPassengers( $request );

            return new OkResponse( $this->loadModel( $this->model->id ) );
        } );
    }

    /**
     * @param Request $request
     * @return FlightController
     */
    private function addNew(Request $request)
    {
        $this->model = $this->getItinerary()->itinerary_flights()->create(
            [
                'booking_category_id' => BookingCategory::Flight,
                'sorting_rank' => $this->getItinerary()->getNextSortingRankFor( Carbon::createFromFormat('Y-m-d',$request->input('segments.0.departure_day')  ) ),

                'confirmation_number' => $request->input( 'confirmation_number' ),
                'cancel_policy' => $request->input( 'cancel_policy' ),
                'notes' => $request->input( 'notes' ),
                'price' => $request->input( 'price' ),
                'check_in_url' => $request->input( 'check_in_url' ),

            ]
        );

        return $this;
    }

    /**
     * Create Passengers if correctly passed in as array
     *
     * @param Request $request
     * @return $this
     * @throws \Exception
     */
    private function createPassengers(Request $request )
    {
        $passengers = $request->input('passengers' );
        if( $passengers && count( $passengers ) ) {
            ItineraryItemsController::validatePassengers($passengers);

            foreach ($passengers as $passenger)
                $this->model->flight_passengers()->create(
                    array_merge(
                        [
                            "itinerary_passenger_id" => ItineraryPassenger::updateOrCreate($this->model->itinerary, $passenger)->id,
                        ],
                        $passenger
                    )
                );
        }

        return $this;
    }

    /**
     * Create Segments if correctly passed in as array
     *
     * @param Request $request
     * @return $this
     * @throws \Exception
     */
    private function createSegments(Request $request ): FlightController
    {
        $segments = $request->input('segments' );
        if( $segments && count( $segments ) ) {
            $flightSegmentController = new FlightSegmentController();
            foreach ($segments as $segment)
            {
                Validator::validate( $segment, $flightSegmentController->getCommonRules()  );
                $flightSegmentController->createSegment( $this->model,$segment );
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function update( Request $request )
    {
        $this->validatedRules($this->getCommonRules());

        $this->model->update(
            [
                'confirmation_number' => $request->input( 'confirmation_number' ),
                'cancel_policy' => $request->input( 'cancel_policy' ),
                'notes' => $request->input( 'notes' ),
                'price' => $request->input( 'price' ),
                'check_in_url' => $request->input( 'check_in_url' ),
                'custom_header_title' => $request->input('custom_header_title'),
            ]
        );

        return $this->loadModel( $this->model->id, true );
    }

    /**
     * @inheritDoc
     */
    public function delete()
    {
        Storage::cloud()->deleteDirectory($this->model->getFolderStorageRelativePath());
        return parent::delete();
    }
}
