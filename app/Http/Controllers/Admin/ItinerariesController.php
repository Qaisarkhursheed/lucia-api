<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Enhancers\YajraPaginableTraitController;
use App\ModelsExtended\Itinerary;
use App\ModelsExtended\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ItinerariesController extends Controller
{
    use YajraPaginableTraitController;

    /**
     * @var Authenticatable|null|User
     */
    private $user;

    public function __construct()
    {
        $this->user = auth()->user();
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|LengthAwarePaginator|Builder[]|Collection|Response
     */
    public function __invoke(Request $request)
    {
        return $this->paginateYajra(  );
    }

    /**
     * @inheritDoc
     */
    protected function getQuery(): Builder
    {
        return Itinerary::query()
            ->join("users as creator" , "creator.id", "=" ,"itinerary.user_id" )

            ->when($this->user->isLoggedInAsMasterAccount(), function ( Builder $builder){
                $builder->whereHas( "user.master_sub_account", function (Builder $builder){
                    $builder->where( "master_sub_account.master_account_id", $this->user->masterAccountId() );
                });
            })

            ->join("itinerary_status" , "itinerary_status.id", "=" ,"itinerary.status_id" )
            ->join("traveller" , "traveller.id", "=" ,"itinerary.traveller_id" )
            ->leftJoin("view_latest_client_emails" , "view_latest_client_emails.itinerary_client_id", "=" ,"itinerary.traveller_id" )
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
                'creator.name as created_by',
                'creator.account_status_id',
                'creator.email as creator_email',
                'traveller.id as itinerary_client_id',
            );
    }

    /**
     * @param Builder $query
     * @return Builder|mixed
     */
    protected function filterQuery(Builder $query)
    {
        return $query->when($this->search, function (Builder $builder) {
            $search = $this->search;
            $builder->where("itinerary.title", 'like', "%$search%")
                ->orWhere("traveller.name", 'like', "%$search%")
                ->orWhere("creator.name", 'like', "%$search%");
        });
    }
}
