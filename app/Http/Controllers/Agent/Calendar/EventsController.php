<?php

namespace App\Http\Controllers\Agent\Calendar;

use App\Http\Controllers\Controller;
use App\ModelsExtended\Itinerary;
use App\ModelsExtended\ItineraryStatus;
use Illuminate\Database\Eloquent\Builder;

class EventsController extends Controller
{
    public function __invoke( )
    {
        $this->validatedRules(
            [
                'from' => 'required|date_format:Y-m-d',
                'to' => 'required|date_format:Y-m-d|after_or_equal:from',
            ]
        );
        $query =  Itinerary::query()
            ->where( "itinerary.status_id", ItineraryStatus::Accepted )
            ->where( "itinerary.user_id", auth()->id() )
            ->where( function ( Builder  $builder  ){
                $builder->where(function (Builder $builder) {
                    $builder->where("start_date", ">=", request("from"))
                        ->where("start_date", "<=", request("to"));
                })
                    ->orWhere(function (Builder $builder) {
                        $builder->where("end_date", ">=", request("from"))
                            ->where("end_date", "<=", request("to"));
                    });
            });

        return $query->get()->map(fn(Itinerary $itinerary) => $itinerary->formatForCalendar() );
    }
}
