<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Agent\Itinerary\ClientEmailController;
use App\Http\Controllers\Enhancers\CRUDEnabledController;
use App\Http\Controllers\Enhancers\YajraPaginableTraitController;
use App\ModelsExtended\Itinerary;
use App\ModelsExtended\Traveller;
use App\Rules\PhoneNumberValidationRule;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Nette\NotImplementedException;

/**
 * @property Traveller $model
 */
class TravellersController extends CRUDEnabledController
{
    use YajraPaginableTraitController;

    public function __construct()
    {
        parent::__construct( "traveller_id", "traveller.id" );
    }

    public function getCommonRules()
    {
        return [
            'phone' => [ 'nullable', 'max:30', new PhoneNumberValidationRule() ],
            'name' => 'required|string|max:150',
            'abstract_note' => 'nullable|string|max:3000',
            'emails' => 'filled|array|max:' . ClientEmailController::MAXIMUM_EMAILS,
            'birthday' => 'filled|date_format:Y-m-d|before:today',
            'address' => 'nullable|max:300',
             'image' => 'filled|image|max:20000',    // 20MB
        ];
    }

    public function fetchAll()
    {
        return $this->paginateYajra( );
    }

    /**
     * @inheritDoc
     */
    protected function getQuery(): Builder
    {
        return Traveller::query()
            ->leftJoin( "view_latest_client_emails", "view_latest_client_emails.itinerary_client_id", "=", "traveller.id" )
            ->where( "traveller.created_by_id" , auth()->id()  )
            ->select(
                DB::raw( '(select count(*) from itinerary where itinerary.traveller_id = traveller.id) as itineraries_count' ),
                'traveller.id',
                'traveller.created_at',
                'traveller.name',
                'traveller.birthday',
                'view_latest_client_emails.email',
                'traveller.phone',
            );
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function store(Request $request)
    {
        $this->validatedRules( $this->getCommonRules() );

        if (!$this->isValidEmailArray($request->input('emails')))
            throw new \Exception("Please make sure all entries are valid emails.");

        $this->model = Traveller::createOrUpdateTraveller(
            $request->input('name'),
            $request->input('phone'),
            $request->input('abstract_note'),
            $request->input('birthday') ? Carbon::createFromFormat("Y-m-d", $request->input('birthday')) : null
        );

        $this->model->address = $request->input('address', $this->model->address );
        $this->model->setImage( $request->file('image') );
        $this->model->update();

        return $this->createClientEmails($request)->fetch();
    }

    /**
     * @param Request $request
     * @return TravellersController
     */
    private function createClientEmails(Request $request): TravellersController
    {
        $this->model->traveller_emails()->delete();

        if(!$request->input( 'emails' )) return $this;

        $this->model->traveller_emails()->createMany(
            collect( $request->input( 'emails' ) )
                ->map( function ( string $email ) { return [ "email" => $email ]; } )
                ->toArray()
        );

        return $this;
    }

    /**
     * Update loaded resource / model
     *
     * @param Request $request
     * @throws NotImplementedException
     * @throws ValidationException
     * @throws \Exception
     */
    public function update( Request $request ){
        $this->validatedRules( $this->getCommonRules() );
        parent::update($request);

        $this->model->setImage( $request->file('image') );
        $this->model->update();

        if ($request->input('emails') &&
            !$this->isValidEmailArray($request->input('emails')))
            throw new \Exception("Please make sure all entries are valid emails.");

        return $this->createClientEmails($request)->fetch();
    }

    /**
     * @param Builder $query
     * @return Builder|mixed
     */
    protected function filterQuery(Builder $query)
    {
        return $query->when($this->search, function (Builder $builder) {
            $search = $this->search;
            $builder->where("traveller.name", 'like', "%$search%");
        });
    }

    public function showItineraries()
    {
        return Itinerary::query()
            ->join("itinerary_status" , "itinerary_status.id", "=" ,"itinerary.status_id" )
            ->join("traveller" , "itinerary.traveller_id", "=" ,"traveller.id" )
            ->leftJoin("view_latest_client_emails" , "view_latest_client_emails.itinerary_client_id", "=" ,"itinerary.traveller_id" )
            ->where( "itinerary.user_id", auth()->id() )
            ->where( "itinerary.traveller_id", $this->routeParameterValue )
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
            )
            ->get();
    }

    public function fetch()
    {
        return $this->model->refresh()->load("traveller_emails")->presentForDev();
    }

    /**
     * @inheritDoc
     */
    public function getDataQuery(): Builder
    {
        return $this->getQuery();
    }
}
