<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings\Flights;

use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Agent\Itinerary\Bookings\FlightItemsController;
use App\Http\Responses\OkResponse;
use App\ModelsExtended\ItineraryFlight;
use App\ModelsExtended\ItineraryFlightSegment;
use App\ModelsExtended\ModelBase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * @property ItineraryFlightSegment $model
 */
class FlightSegmentController extends FlightItemsController
{
    public function __construct()
    {
        parent::__construct( "flight_segment_id" );
    }

    /**
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function fetchAll()
    {
        return $this->getDataQuery()->get()->map->presentForDev();
    }

    public function fetch()
    {
        return new OkResponse( $this->model->presentForDev() );
    }

    /**
     * @inheritDoc
     */
    public function getCommonRules()
    {
        return [
            'flight_from' => 'required|string|max:150',
            'flight_to' => 'required|string|max:150',
            'flight_number' => 'required|string|max:50',
            'airline' => 'required|string|max:150',
            'airline_operator' => 'nullable|string|max:150',

            'departure_time' => 'required|date_format:h\:i\ A',
            'arrival_time' => 'required|date_format:h\:i\ A',

            'departure_day' => 'required|date_format:Y-m-d',
            'arrival_day' => 'required|date_format:Y-m-d',

        ];
    }

    /**
     * @inheritDoc
     */
    public function store( Request $request )
    {
        return $this->createSegment( $this->getFlight(), $this->validatedRules($this->getCommonRules()) )
            ->presentForDev();
    }

    /**
     * @return OkResponse|void
     * @throws RecordNotFoundException
     */
    public function delete()
    {
        if( $this->model->itinerary_flight->itinerary_flight_segments->count() < 2 )
            throw new \Exception( "You must have at least 1 segment in the flight!" );
        parent::delete();
    }

    /**
     * @param ItineraryFlight|ModelBase $flight
     * @param array $inputArray
     * @return \Illuminate\Database\Eloquent\Model|ItineraryFlightSegment
     */
    public function createSegment( ItineraryFlight $flight, array $inputArray)
    {
        $departure_time = Carbon::createFromTimeString( $inputArray[ 'departure_time' ] );
        $arrival_time = Carbon::createFromTimeString( $inputArray[ 'arrival_time' ] );

        $departure_day = Carbon::createFromFormat( 'Y-m-d', $inputArray[ 'departure_day' ]  );
        $arrival_day = Carbon::createFromFormat( 'Y-m-d', $inputArray[ 'arrival_day' ]  );

        $departure_datetime =  $departure_day->clone()->setTimeFrom( $departure_time )->fromPreferredTimezoneToAppTimezone();
        $arrival_datetime =  $arrival_day->clone()->setTimeFrom( $arrival_time )->fromPreferredTimezoneToAppTimezone();

        return $flight->itinerary_flight_segments()->create(
            [
                'flight_from' => $inputArray[ 'flight_from' ],
                'flight_to' => $inputArray[ 'flight_to' ],
                'airline' => $inputArray[ 'airline' ],
                'airline_operator' => array_key_exists( 'airline_operator', $inputArray )? $inputArray['airline_operator'] : null ,
                'duration_in_minutes' => $departure_datetime->diffInMinutes( $arrival_datetime ),
                'flight_number' => $inputArray[ 'flight_number' ],
                'departure_datetime' =>  $departure_datetime,
                'arrival_datetime' =>  $arrival_datetime,
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function update( Request $request )
    {
        $this->validatedRules($this->getCommonRules()) ;

        $departure_time = Carbon::createFromTimeString( $request->input( 'departure_time' ) );
        $arrival_time = Carbon::createFromTimeString( $request->input( 'arrival_time' ) );

        $departure_day = Carbon::createFromFormat( 'Y-m-d', $request->input( 'departure_day' )  );
        $arrival_day = Carbon::createFromFormat( 'Y-m-d', $request->input( 'arrival_day' )  );

        $departure_datetime =  $departure_day->clone()->setTimeFrom( $departure_time )->fromPreferredTimezoneToAppTimezone();
        $arrival_datetime =  $arrival_day->clone()->setTimeFrom( $arrival_time )->fromPreferredTimezoneToAppTimezone();


        $this->model->update(
            [
                'flight_from' => $request->input( 'flight_from' ),
                'flight_to' => $request->input( 'flight_to' ),
                'airline' => $request->input( 'airline' ),
                'airline_operator' => $request->input('airline_operator'),
                'duration_in_minutes' => $departure_datetime->diffInMinutes( $arrival_datetime ),
                'flight_number' => $request->input( 'flight_number' ),
                'departure_datetime' =>  $departure_datetime,
                'arrival_datetime' =>  $arrival_datetime,
            ]
        );

        return $this->loadModel( $this->model->id )->presentForDev();
    }

    /**
     * @inheritDoc
     */
    public function loadModel( $route_param_value, bool $withRelations = true )
    {
        $this->model = $this->getDataQuery()
            ->where("id", $route_param_value)
            ->first();

        if( ! $this->model ) throw new RecordNotFoundException();

        return $this->model;
    }

    /**
     * @inheritDoc
     */
    public function getDataQuery(): Builder
    {
        return  ItineraryFlightSegment::query()
            ->whereHas( "itinerary_flight.itinerary" , function ( Builder $builder ) {
                $builder->where( "itinerary.id", $this->getItineraryId() )
                    ->where( "itinerary.user_id", auth()->id() );
            })
            ->where( "itinerary_flight_id", $this->getFlightId() );
    }
}
