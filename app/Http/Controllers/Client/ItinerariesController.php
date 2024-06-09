<?php

namespace App\Http\Controllers\Client;

use App\ModelsExtended\Itinerary;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class ItinerariesController extends \App\Http\Controllers\Agent\ItineraryController
{
    use MyItinerariesQueryTrait;

    public function fetchAll()
    {
        return [
            "trips" => $this->getCurrentOrFutureTrips(request('search')),
            "past_trips" => $this->getPastTrips(request('search')),
        ];
    }

    public function fetch()
    {
        return $this->myItineraries()
            ->where( "id", $this->routeParameterValue )
            ->firstOrFail()
            ->packForFetch();
    }

    private function getCurrentOrFutureTrips(?string $search)
    {
        return $this->myItineraries()
            ->when($search, function ( Builder $builder ) use ($search){
                $builder->where("itinerary.title", "like", "%$search%" );
            } )
            ->whereDate("end_date" , ">=", Carbon::now() )
            ->get()->map(fn ( Itinerary $itinerary) => $this->formatForShortList($itinerary) );
    }

    private function getPastTrips(?string $search)
    {
        return $this->myItineraries()
            ->when($search, function ( Builder $builder ) use ($search){
                $builder->where("itinerary.title", "like", "%$search%" );
            } )
            ->whereDate("end_date" , "<=", Carbon::now() )
            ->get()->map(fn ( Itinerary $itinerary) => $this->formatForShortList($itinerary));
    }

    private function formatForShortList(Itinerary $itinerary)
    {
        return Arr::only( $itinerary->formatForSharing(), [
            'identification', 'title', 'start_date',
            'end_date', 'pictures', 'status',
            'abstract_note', 'sharing'
        ] );
    }
}
