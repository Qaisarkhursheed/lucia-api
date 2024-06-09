<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings;

use App\Http\Controllers\Agent\Itinerary\Bookings\Cruises\CabinsController;
use App\Http\Controllers\Agent\Itinerary\ItineraryItemsController;
use App\Http\Middleware\ConvertStringsToBooleanMiddleware;
use App\Http\Responses\OkResponse;
use App\ModelsExtended\BookingCategory;
use App\ModelsExtended\ItineraryCruise;
use App\ModelsExtended\ItineraryPassenger;
use App\Rules\PhoneNumberValidationRule;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * @property ItineraryCruise $model
 */
class CruiseController extends ItineraryItemsController
{
    public function __construct()
    {
        $this->middleware( ConvertStringsToBooleanMiddleware::class );

        parent::__construct( "cruise_id" );
    }

    public function getDataQuery(): Builder
    {
        return  $this->limitBookingToItineraryOwner( ItineraryCruise::with( "cruise_passengers", "cruise_pictures",
            "cruise_passengers.itinerary_passenger", "cruise_supplier" ))
            ->where( "itinerary_id", $this->getItineraryId() );
    }

    public function getCommonRules()
    {
        return [
            'cruise_ship_name' => 'required|string',
            'departure_port_city' => 'required|string',
            'arrival_port_city' => 'required|string',

            'departure_time' => 'required|date_format:h\:i\ A',
            'disembarkation_time' => 'required|date_format:h\:i\ A',

            'departure_day' => 'required|date_format:Y-m-d',
            'disembarkation_day' => 'required|date_format:Y-m-d|after_or_equal:departure_day',

            'notes' => 'nullable|string|max:3000',
            'cancel_policy' => 'nullable|string|max:3000',
            'custom_header_title' => 'nullable|string|max:250',

        ];
    }

    public function fetch()
    {
        return new OkResponse( $this->model->presentForDev() );
    }

    public function fetchAll()
    {
        return parent::fetchAll()->map->presentForDev();
    }

    /**
     * @inheritDoc
     */
    public function store( Request $request )
    {
        $this->validatedRules( array_merge( $this->getCommonRulesWithProvider(), [
            'passengers' => 'nullable|array|max:100',
            'cabins' => 'nullable|array|max:100',
        ]));

        return DB::transaction(function () use ($request) {
            $this->addNew( $request )
                ->createProvider( $request )
                ->createCabins( $request )
                ->createPassengers( $request );

            $this->model->refresh();

            return  $this->fetch();
        } );
    }

    /**
     * @param Request $request
     * @return $this
     */
    private function createCabins(Request $request)
    {
        $rooms = $request->input('cabins' );
        if( $rooms && count( $rooms ) ) {
            $RoomsController = new CabinsController();
            foreach ($rooms as $index => $room )
            {
                Validator::validate( $room , $RoomsController->getCommonRules() );
                $this->model->cruise_cabins()->create($room);
            }
        }
        return $this;
    }

    /**
     * @param Request $request
     * @return CruiseController
     */
    private function addNew(Request $request)
    {
        $departure_time = Carbon::createFromTimeString( $request->input( 'departure_time' ) );
        $disembarkation_time = Carbon::createFromTimeString( $request->input( 'disembarkation_time' ) );

        $departure_day = Carbon::createFromFormat( 'Y-m-d', $request->input( 'departure_day' )  );
        $disembarkation_day = Carbon::createFromFormat( 'Y-m-d', $request->input( 'disembarkation_day' )  );

        $this->model = $this->getItinerary()->itinerary_cruises()->create(
            [
                'booking_category_id' => BookingCategory::Cruise,
                'sorting_rank' => $this->getItinerary()->getNextSortingRankFor( Carbon::createFromFormat('Y-m-d',$request->input('departure_day')  ) ),

                'cruise_ship_name' => $request->input('cruise_ship_name'),
                'departure_port_city' => $request->input('departure_port_city'),
                'arrival_port_city' => $request->input('arrival_port_city'),
                'departure_datetime' => $departure_day->clone()->setTimeFrom($departure_time)->fromPreferredTimezoneToAppTimezone(),
                'disembarkation_datetime' => $disembarkation_day->clone()->setTimeFrom($disembarkation_time)->fromPreferredTimezoneToAppTimezone(),
                'cancel_policy' => $request->input('cancel_policy'),
                'notes' => $request->input('notes'),
                'custom_header_title' => $request->input('custom_header_title'),

            ]
        );

        return $this;
    }

    /**
     * Create Passengers if correctly passed in as array
     *
     * @param Request $request
     * @return $this
     */
    private function createPassengers(Request $request)
    {
        $passengers = $request->input('passengers' );
        if( $passengers && count( $passengers ) ) {
            ItineraryItemsController::validatePassengers($passengers);

            foreach ($passengers as $passenger)
                $this->model->cruise_passengers()->create(
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
     * @inheritDoc
     */
    public function update( Request $request )
    {
        $this->validatedRules($this->getCommonRulesWithProviderForUpdate());

        $departure_time = Carbon::createFromTimeString( $request->input( 'departure_time' ) );
        $disembarkation_time = Carbon::createFromTimeString( $request->input( 'disembarkation_time' ) );

        $departure_day = Carbon::createFromFormat( 'Y-m-d', $request->input( 'departure_day' )  );
        $disembarkation_day = Carbon::createFromFormat( 'Y-m-d', $request->input( 'disembarkation_day' )  );

        $this->model->update(
            [
                'cruise_ship_name' => $request->input('cruise_ship_name'),
                'departure_port_city' => $request->input('departure_port_city'),
                'arrival_port_city' => $request->input('arrival_port_city'),
                'departure_datetime' => $departure_day->clone()->setTimeFrom($departure_time)->fromPreferredTimezoneToAppTimezone(),
                'disembarkation_datetime' => $disembarkation_day->clone()->setTimeFrom($disembarkation_time)->fromPreferredTimezoneToAppTimezone(),
                'cancel_policy' => $request->input('cancel_policy'),
                'notes' => $request->input('notes'),
                'custom_header_title' => $request->input('custom_header_title'),

            ]
        );

        return $this->updateProvider( $request )->fetch();
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
