<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings;

use App\Http\Controllers\Agent\Itinerary\ItineraryItemsController;
use App\Http\Middleware\ConvertStringsToBooleanMiddleware;
use App\Http\Responses\OkResponse;
use App\ModelsExtended\BookingCategory;
use App\ModelsExtended\ItineraryPassenger;
use App\ModelsExtended\ItineraryTransport;
use App\ModelsExtended\TransitType;
use App\Rules\PhoneNumberValidationRule;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * @property ItineraryTransport $model
 */
class TransportController extends ItineraryItemsController
{
    public function __construct()
    {
        $this->middleware( ConvertStringsToBooleanMiddleware::class );
        parent::__construct( "transport_id" );
    }

    public function getDataQuery(): Builder
    {
        return  $this->limitBookingToItineraryOwner( ItineraryTransport::with("transit_type" ,"transport_passengers",
            "transport_supplier" , "transport_pictures" ))
            ->where( "itinerary_id", $this->getItineraryId() );
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
     * Check if request is valid
     * @return $this
     * @throws \Illuminate\Validation\ValidationException
     */
    private function isValidStoreRequest()
    {
        switch ( $this->getTransitTypeId() )
        {
            case TransitType::Car:
            case TransitType::Transfer:
                $this->validatedRules([
                    'vehicle' => 'nullable|string',
                ]);

            case TransitType::Ferry:
            case TransitType::Rail:
                $this->validatedRules([
                    'transport_from' => 'nullable|string',
                    'transport_to' => 'nullable|string',

                    'departure_time' => 'filled|date_format:h\:i\ A',
                    'arrival_time' => 'filled|date_format:h\:i\ A',

                    'departure_day' => 'required|date_format:Y-m-d',
                    'arrival_day' => 'required|date_format:Y-m-d|after_or_equal:departure_day',
//
//                    'departure_day' => 'required|date_format:Y-m-d|after_or_equal:today',
//                    'arrival_day' => 'required|date_format:Y-m-d|after_or_equal:departure_day',
                ]);
        }

        return $this;
    }

    public function getCommonRules()
    {
        return [
            'transit_type_id' => 'required|exists:transit_type,id',
            'price' => 'filled|numeric',

            'notes' => 'nullable|string|max:3000',
            'custom_header_title' => 'nullable|string|max:250',

        ];
    }

    /**
     * @inheritDoc
     */
    public function store( Request $request )
    {
        $this->validatedRules( array_merge( $this->getCommonRulesWithOptionalProvider(), ['passengers' => 'nullable|array|max:100',]));

        return DB::transaction(function () use ($request) {
            $this->isValidStoreRequest()
                ->addNew($request)
                ->createProvider($request)
                ->createPassengers($request)
                ->model->refresh();


            return  $this->fetch();
        } );
    }

    /**
     * @return array|Request|\Laravel\Lumen\Application|mixed|int
     */
    private function getTransitTypeId()
    {
        if( $this->model ) return $this->model->transit_type_id;
        return \request( "transit_type_id" );
    }

    /**
     * @param Request $request
     * @return TransportController
     */
    private function addNew(Request $request)
    {
        $dateRange = $this->fetchParsedDateRange($request,
            'departure_day', 'departure_time',
            'arrival_day', 'arrival_time'
        );

        $this->model = $this->getItinerary()->itinerary_transports()->create(
            [
                'booking_category_id' => BookingCategory::Transportation,
                'sorting_rank' => $this->getItinerary()->getNextSortingRankFor( Carbon::createFromFormat('Y-m-d',$request->input('departure_day')  ) ),

                'transit_type_id' => $request->input('transit_type_id') ,
                'price' => $request->input('price') ,
                'notes' => $request->input('notes') ,
                'transport_from' => $request->input('transport_from') ,
                'transport_to' => $request->input('transport_to') ,
                'vehicle' => $request->input('vehicle') ,

                'custom_header_title' => $request->input('custom_header_title'),

                'departure_datetime' => $dateRange["start_datetime"],
                'arrival_datetime' => $dateRange["end_datetime"],

            ]
        );

        return $this;
    }

    /**
     * Create Passengers if correctly passed in as array
     *
     * @param Request $request
     * @param ItineraryTransport $transport
     * @return $this
     */
    private function createPassengers(Request $request)
    {
        $passengers = $request->input('passengers' );
        if( $passengers && count( $passengers ) ) {
            ItineraryItemsController::validatePassengers($passengers);

            foreach ($passengers as $passenger)
                $this->model->transport_passengers()->create(
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
        $this->validatedRules(
            Arr::except( $this->getCommonRulesWithProvider(), [ 'provider_name', 'save_to_library', 'transit_type_id' ] ));
        $this->isValidStoreRequest();

        $dateRange = $this->fetchParsedDateRange($request,
            'departure_day', 'departure_time',
            'arrival_day', 'arrival_time'
        );

        $this->model->update(
            [
                'price' => $request->input('price') ,
                'notes' => $request->input('notes') ,
                'transport_from' => $request->input('transport_from') ,
                'transport_to' => $request->input('transport_to') ,
                'vehicle' => $request->input('vehicle') ,
                'custom_header_title' => $request->input('custom_header_title'),

                'departure_datetime' => $dateRange["start_datetime"],
                'arrival_datetime' => $dateRange["end_datetime"],
            ]
        );

        return $this->updateProvider(  $request )->fetch();
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
