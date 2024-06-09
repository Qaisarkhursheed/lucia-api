<?php

namespace App\Http\Controllers\Agent;

use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Agent\Itinerary\ClientEmailController;
use App\Http\Controllers\Agent\Itinerary\ItineraryItemsController;
use App\Http\Controllers\Enhancers\CRUDEnabledController;
use App\Http\Controllers\Enhancers\IYajraEloquentResultProcessorInterface;
use App\Http\Controllers\Enhancers\YajraPaginableTraitController;
use App\Http\Middleware\ConvertStringsToBooleanMiddleware;
use App\Http\Middleware\LaravelMiddleware\ConvertEmptyStringsToNull;
use App\Http\Responses\OkResponse;
use App\Mail\Agent\Itinerary\ShareItineraryMail;
use App\ModelsExtended\CurrencyType;
use App\ModelsExtended\Itinerary;
use App\ModelsExtended\ItineraryStatus;
use App\ModelsExtended\ItineraryTheme;
use App\ModelsExtended\ModelBase;
use App\ModelsExtended\Traveller;
use App\ModelsExtended\User;
use App\Repositories\SMS\ISMSSender;
use App\Rules\PhoneNumberValidationRule;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

/**
 * @property Itinerary $model
 */
class ItineraryController extends CRUDEnabledController implements IYajraEloquentResultProcessorInterface
{
    use YajraPaginableTraitController;

    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable|null | User
     */
    protected $user;

    public function __construct()
    {
        $this->middleware( ConvertStringsToBooleanMiddleware::class );
        $this->middleware( ConvertEmptyStringsToNull::class );
        $this->user = auth()->user();

        parent::__construct( "itinerary_id" );
    }

    public function getCommonRules()
    {
        return [
            'title' => 'required|string|max:150',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'abstract_note' => 'nullable|string|max:3000',
            'show_price_on_share' => 'nullable|boolean',
            'total_price' => 'nullable|string|max:50',

            'hide_abstract' => 'nullable|boolean',
            'abstract_position_id' => 'filled|exists:property_position,id',
            'currency_id' => 'filled|exists:currency_types,id',
            'property_design_id' => 'filled|exists:property_design,id',
            'itinerary_logo' => 'filled|image|max:20000',    // 20MB

            'mark_as_client_approved' => 'nullable|boolean',

            'client_phone' => [ 'nullable', 'max:30' ],
            'client_name' => 'required|string|max:150',
            'client_emails' => 'nullable|array|max:' . ClientEmailController::MAXIMUM_EMAILS,
        ];
    }

    public function fetch()
    {
        if ( is_true( \request('detailed' ) ) )
        {
            if( is_true( \request('shared_view' ) ) )
            {
                if( is_true( \request('packed_booking' ) ) )
                    return  $this->model->packForFetch();

                return  $this->model->formatForSharing();
            }

            $immediate_relations = [
                'currency_type',
                'traveller', 'traveller.traveller_emails',
                'itinerary_theme', 'itinerary_theme.property_position', 'itinerary_theme.property_design',
                 'itinerary_status',
                "itinerary_passengers","itinerary_passengers.passenger_type",
                "itinerary_pictures"
            ];

            return array_merge(
                $this->model->withoutRelations()->toArray(),
                Arr::only( $this->model->getRelations(), $immediate_relations),
                [
                    "bookings" => Arr::except( $this->model->getRelations(), $immediate_relations)
                ]
            );

        }

        return parent::fetch();
    }

    /**
     * @return array|Builder[]|Collection|JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function fetchAll()
    {
        $this->validatedRules([
            'show_past_itineraries' => 'required|boolean',
            'show_upcoming_itineraries' => 'required|boolean',
            'show_active_itineraries' => 'required|boolean',
        ]);
        return $this->paginateYajra( $this );
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function store( Request $request )
    {
        $this->validatedRules( array_merge( $this->getCommonRules() , [ 'passengers' => 'nullable|array|max:100'] ) );

        $this->validateClientEmailRequest($request);

        return DB::transaction(function () use ($request) {
            $itinerary = $this->addNewItinerary($request);
            $this->createClientEmails($request, $itinerary)
                ->createTheme($request, $itinerary)
                ->createPassengers($request, $itinerary);

            return new OkResponse(
                $this->storeRequestImages( $itinerary )
                ->loadModel( $itinerary->id )
            );
        } );
    }

    /**
     * @param Request $request
     * @return Itinerary
     */
    private function addNewItinerary(Request $request)
    {
        return Itinerary::create(
            [
                'traveller_id' => Traveller::createOrUpdateTraveller(
                    $request->input( 'client_name' ),
                    $request->input( 'client_phone' ))->id,
                'user_id' => auth()->id(),
                'title' => $request->input( 'title' ),
                'start_date' => $request->input( 'start_date' ),
                'status_id' =>  is_true( $request->input( 'mark_as_client_approved' ) ) ? ItineraryStatus::Accepted : ItineraryStatus::Draft,
                'end_date' => $request->input( 'end_date' ),
                'abstract_note' => $request->input( 'abstract_note' ),
                'show_price_on_share' => $request->input( 'show_price_on_share' ),
                'total_price' => $request->input( 'total_price' ),
                'mark_as_client_approved' => $request->input( 'mark_as_client_approved' ),
                'currency_id' => $request->input( 'currency_id', CurrencyType::USD ),
            ]
        );
    }

    /**
     * @param Request $request
     * @param Itinerary|ModelBase $itinerary
     * @return $this
     */
    private function createClientEmails(Request $request, Itinerary $itinerary): ItineraryController
    {
        $itinerary->traveller->traveller_emails()->delete();
        if(  $request->input( 'client_emails' ) )
        {
            $itinerary->traveller->traveller_emails()->createMany(
                collect( $request->input( 'client_emails' ) )
                    ->map( function ( string $email ) { return [ "email" => $email ]; } )
                    ->toArray()
            );
        }

        return $this;
    }

    /**
     * @param Request $request
     * @param Itinerary $itinerary
     * @return $this
     */
    private function createTheme(Request $request, Itinerary $itinerary): ItineraryController
    {
        $itinerary->itinerary_theme()->create(
            [
                'abstract_position_id' => $request->input( 'abstract_position_id' ),
                'hide_abstract' => $request->input( 'hide_abstract' ),
                'property_design_id' => $request->input( 'property_design_id' , optional($this->user->default_itinerary_theme)->property_design_id),
                "itinerary_logo_url" => $request->hasFile( 'itinerary_logo' ) ? ItineraryTheme::generateItineraryLogoUrlFullPath($itinerary) : null
            ]
        );

        return $this;
    }

    /**
     * Create Passengers if correctly passed in as array
     *
     * @param Request $request
     * @param Itinerary $itinerary
     * @return $this
     * @throws \Exception
     */
    private function createPassengers(Request $request, Itinerary $itinerary)
    {
        $passengers = $request->input('passengers' );
        if( $passengers && count( $passengers ) )
        {
            ItineraryItemsController::validatePassengers( $passengers );
            $itinerary->itinerary_passengers()->createMany( $passengers );
        }

        return $this;
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function update( Request $request )
    {
        $this->validatedRules( $this->getCommonRules() );

        $this->validateClientEmailRequest($request);

        return DB::transaction(function () use ($request) {

            $this->model->traveller->update(
                [
                    'name' => $request->input( 'client_name' ),
                    'phone' => $request->input( 'client_phone' ),
                ]
            );

            $this->createClientEmails($request, $this->model );

            $this->model->update(
                [
                    'title' => $request->input( 'title' ),
                    'start_date' => $request->input( 'start_date' ),
                    'end_date' => $request->input( 'end_date' ),
//                'abstract_note' => $request->input( 'abstract_note' ), moved to separate api
                    'show_price_on_share' => $request->input( 'show_price_on_share' ),
                    'total_price' => $request->input( 'total_price' ),
                    'mark_as_client_approved' => $request->input( 'mark_as_client_approved' ),
                    'currency_id' => $request->input( 'currency_id', CurrencyType::USD ),
                    'status_id' =>  is_true( $request->input( 'mark_as_client_approved' ) ) ? ItineraryStatus::Accepted : ItineraryStatus::Draft,
                ]
            );

            $this->model->itinerary_theme->update(
                array_merge(
                    $request->all(),
                    [
                        "itinerary_logo_url" => $request->hasFile( 'itinerary_logo' ) && ! $this->model->itinerary_theme->itinerary_logo_url
                            ? ItineraryTheme::generateItineraryLogoUrlFullPath($this->model)
                            : $this->model->itinerary_theme->itinerary_logo_url
                    ]
                )
            );


            return $this->storeRequestImages( $this->model )
                ->loadModel( $this->model->id, true );

        });
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function updateAbstract( Request $request )
    {
        $this->validatedRules(
            Arr::only($this->getCommonRules(), [
                'abstract_note', 'abstract_position_id', 'hide_abstract'
            ])
        );
        $this->model->update(
            [
                'abstract_note' => $request->input('abstract_note'),
            ]
        );

        $this->model->itinerary_theme->update($request->all());

        return $this->loadModel($this->model->id);
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function updateLogo( Request $request )
    {
        $this->validatedRules( Arr::only($this->getCommonRules(), [ 'itinerary_logo' ]) );

        if( $request->hasFile( 'itinerary_logo' )  )
        {
            $this->model->itinerary_theme->update(
                [
                    "itinerary_logo_url" => ! $this->model->itinerary_theme->itinerary_logo_url
                        ? ItineraryTheme::generateItineraryLogoUrlFullPath($this->model)
                        : $this->model->itinerary_theme->itinerary_logo_url
                ]
            );

            $this->storeRequestImages( $this->model );
            $this->model->refresh();
        }
        return $this->model->presentForDev();
    }

    /**
     * @inheritDoc
     */
    public function delete()
    {
        // not deleting directory because am using soft delete here
        Storage::cloud()->deleteDirectory($this->model->getFolderStorageRelativePath());
        $this->model->forceDelete();

        return new OkResponse( );
    }

    /**
     * Set itinerary status from within the application
     * @param Request $request
     * @return \App\ModelsExtended\ModelBase|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|mixed|object|null
     * @throws RecordNotFoundException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function setStatus( Request $request )
    {
        $this->validatedRules([
            'status_id' => 'required|exists:itinerary_status,id',
        ]);

        $this->model->update(
            [
                'mark_as_client_approved' =>
                    $request->input( 'status_id' ) == ItineraryStatus::Accepted ||  $request->input( 'status_id' ) == ItineraryStatus::Sent,
                'status_id' => $request->input( 'status_id' ),
            ]
        );

        return $this->loadModel( $this->model->id, true );
    }

    /**
     * @return OkResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function getShareCode()
    {
        // At what stage can I share
        $this->validatedRules([
            'regenerate' => 'nullable|boolean',
        ]);

        if( \request()->input( "regenerate" ) || ! $this->model->share_itinerary_key )
            $this->model->generateShareKey(true);

        return new OkResponse( $this->model->getSharing() );
    }

    /**
     * @param Request $request
     * @return OkResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function sendSharedInvitation(Request $request)
    {
        //
        $this->validatedRules([
            'emails' => 'required|array|min:1',
            'message' => 'nullable|string|max:3000',
        ]);

        if( ! $this->isValidEmailArray( $request->input( 'emails' ) ) )
            throw new \Exception( "Please enter at least 1 email in the array of the client's email and make sure they are all valid emails." );

        $generateResult = $this->getShareCode();

        $this->model->update(['status_id' => ItineraryStatus::Sent]);

        Mail::send(
            new ShareItineraryMail(  $request->input( 'emails' ) ,
                $request->input( 'message' ),
                $this->model
            )
        );

        return $generateResult;
    }

    /**
     * @param Request $request
     * @return OkResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function sendInvitationToClient(Request $request, ISMSSender $ISMSSender)
    {
        $this->validatedRules([
            'phone' => [ 'required', 'max:30', new PhoneNumberValidationRule() ],
            'message' => 'nullable|string|max:450',
        ]);

//        if( ! $this->model->traveller->phone ) throw new \Exception( "Please, update client's phone number." );

        $generateResult = $this->getShareCode();

        $this->model->update(['status_id' => ItineraryStatus::Sent]);

        $ISMSSender->sendSMS(
            $request->input( 'phone' ),
            sprintf(
                "%s\n %s" ,
                $request->input( 'message', '' ),
                 uiAppUrl( 'public/itinerary/' ) . $this->model->share_itinerary_key
            )
        );

        return $generateResult;
    }

    /**
     * @return OkResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function deleteShareCode()
    {
        $this->model->update([ "share_itinerary_key" => null ]);

        return new OkResponse( message( "Shared key deleted" ) );
    }

    /**
     * @inheritDoc
     */
    public function loadModel( $route_param_value, bool $withRelations = true )
    {
        $query = Itinerary::query()

            ->when( $this->user->isLoggedInAsTravelAgent(), function ( Builder $builder ){
                $builder->where( "itinerary.user_id", $this->user->id );
            } )

            ->where("id", $route_param_value );

        if(  $withRelations )
        {
            $query->with(
                'currency_type',
                'traveller', 'traveller.traveller_emails',
                'itinerary_theme', 'itinerary_theme.property_position', 'itinerary_theme.property_design',
                'itinerary_status',
                "itinerary_passengers","itinerary_passengers.passenger_type",
                "itinerary_pictures"
            );
            if ( is_true( \request('detailed' ) ) )
                $query->with(

                    "itinerary_concierges",
                    "itinerary_concierges.concierge_pictures", "itinerary_concierges.concierge_supplier",

                    "itinerary_cruises",
                    "itinerary_cruises.cruise_passengers", "itinerary_cruises.cruise_pictures", "itinerary_cruises.cruise_supplier",

                    "itinerary_flights",
                    "itinerary_flights.itinerary_flight_segments",

                    "itinerary_flights.flight_passengers", "itinerary_flights.flight_pictures", "itinerary_flights.flight_supplier",

                    "itinerary_hotels",
                    "itinerary_hotels.hotel_amenities",
                    "itinerary_hotels.hotel_rooms", "itinerary_hotels.hotel_rooms.currency_type",
                    "itinerary_hotels.hotel_passengers", "itinerary_hotels.hotel_pictures", "itinerary_hotels.hotel_supplier",

                    "itinerary_insurances",
                    "itinerary_insurances.insurance_pictures", "itinerary_insurances.insurance_supplier",

                    "itinerary_others",

                    "itinerary_tours", "itinerary_tours.tour_pictures", "itinerary_tours.tour_supplier",

                    "itinerary_transports",
                    "itinerary_transports.transport_passengers", "itinerary_transports.transport_pictures", "itinerary_transports.transport_supplier",


                );
        }

        $this->model = $query->first();

        if( ! $this->model ) throw new RecordNotFoundException();

        return $this->model;
    }

    /**
     * YajraPaginateTrait
     * @inheritDoc
     */
    protected function getQuery(): Builder
    {
        return Itinerary::query()
            ->join("itinerary_status" , "itinerary_status.id", "=" ,"itinerary.status_id" )
            ->join("traveller" , "itinerary.traveller_id", "=" ,"traveller.id" )
            ->leftJoin("view_latest_client_emails" , "view_latest_client_emails.itinerary_client_id", "=" ,"itinerary.traveller_id" )

            ->when( $this->user->isLoggedInAsTravelAgent(), function ( Builder $builder ){
                $builder->where( "itinerary.user_id", $this->user->id );
            } )

            ->when( $this->getRequestActionCalled() === "fetchAll", function ( Builder $builder ){

                    $builder->where(function ( Builder $builder ) {

                        $builder->when(\request('show_past_itineraries'), function (Builder $builder) {
                            $builder->orWhereDate("itinerary.end_date", '<=', Carbon::now());
                        });

                        $builder->when(\request('show_upcoming_itineraries'), function (Builder $builder) {
                            $builder->orWhereDate("itinerary.start_date", '>', Carbon::now());
                        });

                        $builder->when(\request('show_active_itineraries'), function (Builder $builder) {
                            $builder->orWhere(function (Builder $builder) {
                                $builder->whereDate("itinerary.start_date", '<=', Carbon::now())
                                    ->whereDate("itinerary.end_date", '>=', Carbon::now());
                            });
                        });

                        // If none is selected, disable it
                        $builder->when(
                            !\request('show_past_itineraries')
                            && !\request('show_upcoming_itineraries')
                            && !\request('show_active_itineraries')
                            , function (Builder $builder) {
                            $builder->whereRaw("1=0");
                        });

                    });
            })
            ->select(
                "itinerary_status.id as itinerary_status_id",
                DB::raw("lpad( cast( itinerary.id as NCHAR ), 4, '0' ) as itinerary_identification" ),
                'itinerary.title as itinerary_name',
                'itinerary.created_at',
                'itinerary.start_date',
                'itinerary.end_date',
                'traveller.name as client_name',
                'view_latest_client_emails.email as client_email',
                'itinerary_status.description as status',
                'itinerary.id',
                'traveller.id as itinerary_client_id',
            );
    }

    /**
     * @inheritDoc
     */
    protected function filterQuery(Builder $query)
    {
        return $query->when($this->search, function (Builder $builder) {
            $search = $this->search;
            $builder->where("itinerary.title", 'like', "%$search%")
                ->orWhere("traveller.name", 'like', "%$search%")
                ->orWhere("itinerary_status.description", 'like', "%$search%");
        });
    }

    /**
     * @inheritDoc
     */
    public function getDataQuery(): Builder
    {
        return $this->getQuery();
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    private function validateClientEmailRequest(Request $request): void
    {
        if ($request->input('client_emails') &&
            !$this->isValidEmailArray($request->input('client_emails')))
            throw new \Exception("Please make sure all entries are valid emails.");
    }

    /**
     * @param Itinerary | ModelBase $itinerary
     * @return $this
     */
    private function storeRequestImages(Itinerary $itinerary)
    {
        if( \request( )->hasFile( 'itinerary_logo' ) )
        {
            Storage::cloud()->put(
                ModelBase::getStorageRelativePath( $itinerary->itinerary_theme->itinerary_logo_url ),
                \request()->file('itinerary_logo')->getContent()
            );
        }
        return $this;
    }

//    /**
//     * Drag and Drop Feature
//     * @return OkResponse|array|array[]
//     * @throws \Illuminate\Validation\ValidationException
//     */
//    public function updateBookingDate()
//    {
//        $this->validatedRules(
//            [
//                'start_date' => 'required|date_format:Y-m-d',
//                'category_booking_id' => 'required|numeric',
//                'booking_category_id' => 'required|exists:booking_category,id',
//            ]
//        );
//
//       $shiftableBooking = $this->getBooking();
//       $shiftableBooking->moveStartDate( Carbon::createFromFormat( 'Y-m-d', \request( 'start_date' )) )
//           ->update();
//
//        return $this->fetch();
//    }
//
//    /**
//     * @return Builder|\Illuminate\Database\Eloquent\Model|IShiftableBookingInterface
//     * @throws \Exception
//     */
//    private function getBooking()
//    {
//        switch ( \request( 'booking_category_id' ) )
//        {
//            case BookingCategory::Flight:
//                return ItineraryFlight::query()
//                    ->where( "itinerary_id",  $this->routeParameterValue )
//                    ->where( "id",  \request( 'category_booking_id' ) )
//                    ->firstOrFail();
//            case BookingCategory::Hotel:
//                return ItineraryHotel::query()
//                    ->where( "itinerary_id",  $this->routeParameterValue )
//                    ->where( "id",  \request( 'category_booking_id' ) )
//                    ->firstOrFail();
//            case BookingCategory::Concierge:
//                return ItineraryConcierge::query()
//                    ->where( "itinerary_id",  $this->routeParameterValue )
//                    ->where( "id",  \request( 'category_booking_id' ) )
//                    ->firstOrFail();
//            case BookingCategory::Cruise:
//                return ItineraryCruise::query()
//                    ->where( "itinerary_id",  $this->routeParameterValue )
//                    ->where( "id",  \request( 'category_booking_id' ) )
//                    ->firstOrFail();
//            case BookingCategory::Transportation:
//                return ItineraryTransport::query()
//                    ->where( "itinerary_id",  $this->routeParameterValue )
//                    ->where( "id",  \request( 'category_booking_id' ) )
//                    ->firstOrFail();
//            case BookingCategory::Tour_Activity:
//                return ItineraryTour::query()
//                    ->where( "itinerary_id",  $this->routeParameterValue )
//                    ->where( "id",  \request( 'category_booking_id' ) )
//                    ->firstOrFail();
//            case BookingCategory::Insurance:
//                return ItineraryInsurance::query()
//                    ->where( "itinerary_id",  $this->routeParameterValue )
//                    ->where( "id",  \request( 'category_booking_id' ) )
//                    ->firstOrFail();
//            case BookingCategory::Other_Notes:
//                return ItineraryOther::query()
//                    ->where( "itinerary_id",  $this->routeParameterValue )
//                    ->where( "id",  \request( 'category_booking_id' ) )
//                    ->firstOrFail();
//        }
//        throw new \Exception( "Invalid Booking Category Specified!" );
//    }

    /**
     * @return OkResponse
     */
    public function cloneItinerary()
    {
       return DB::transaction(function ( ){
            $newModel = $this->model
                ->duplicateWithRelations()
                ->saveWithRelations();

            return $newModel->presentForDev();
        });
    }

    public function processYajraEloquentResult($result): array
    {
        return $result->toArray();
    }
}
