<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\ModelsExtended\Itinerary;
use App\ModelsExtended\ItineraryClient;
use App\ModelsExtended\ServiceSupplier;
use App\ModelsExtended\Traveller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class GlobalSearchController extends Controller
{
    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __invoke(Request $request)
    {
        $this->validatedRules(
            [
                'query' => 'nullable|string|max:150',
                'only_search' => [ 'filled', 'array', 'min:1',
                    Rule::in( [ 'bookings', 'clients', 'suppliers' ] )
                ]
            ]
        );

        return $this->buildResults();
    }

    /**
     * @return Collection
     */
    public function searchBookings(  ): Collection
    {
        return Itinerary::with("itinerary_client")
            ->where("user_id", auth()->id() )
             ->when( \request('query' ), function (Builder $builder) {

                 $builder->where( function (Builder $builder) {

                     $search = \request('query' );

                     $builder->where("itinerary.title", 'like', "%$search%")
                         ->orWhereHas("itinerary_client", function (Builder $builder) use ( $search ) {
                             $builder->where("itinerary_client.name", 'like', "%$search%");
                         });

                 });
             })
            ->get()
            ->map
            ->globalSearchResultView();
    }

    /**
     * @return Collection
     */
    public function searchSuppliers(  ): Collection
    {
        return ServiceSupplier::with("booking_category")
            ->where( function (Builder $builder) {
                $builder->whereHas( "saved_suppliers" , function ( $builder) {
                    $builder->where( "saved_supplier.user_id" , auth()->id() );
                } );
//
//                $builder->where( "is_globally_accessible" , true )
//                    ->orWhere( "created_by_id" , auth()->id() );
            })
             ->when( \request('query' ), function (Builder $builder) {

                 $builder->where( function (Builder $builder) {
                     $search = \request('query' );
                     $builder->where("name", 'like', "%$search%")
                        ->orWhere("address", 'like', "%$search%");
                 });
             })
            ->get()
            ->map
            ->globalSearchResultView();
    }

    /**
     * @return Collection
     */
    public function searchClients(  ): Collection
    {
        return Traveller::with("defaultEmail" )
             ->where("traveller.created_by_id", auth()->id() )
            ->when( \request('query' ), function (Builder $builder) {

                $builder->where( function (Builder $builder) {

                    $search = \request('query' );

                    $builder->where("traveller.name", 'like', "%$search%")
                        ->orWhere("traveller.phone", 'like', "%$search%")
                        ->orWhereHas("defaultEmail", function (Builder $builder) use ( $search ) {
                            $builder->where("view_latest_client_emails.email", 'like', "%$search%");
                        });
                });
            })
            ->get()
            ->map
            ->globalSearchResultView();
    }

    /**
     * @return array
     */
    private function buildResults(): array
    {
        $collect = collect([]);
        $only_search = \request( 'only_search' );

        if( ! $only_search  || in_array( 'bookings', $only_search ) )
            $collect->put( "bookings" , $this->searchBookings() );

        if( ! $only_search  || in_array( 'clients', $only_search ) )
            $collect->put( "clients" , $this->searchClients() );

        if( ! $only_search  || in_array( 'suppliers', $only_search ) )
            $collect->put( "suppliers" , $this->searchSuppliers() );

        return $collect->toArray();
    }
}
