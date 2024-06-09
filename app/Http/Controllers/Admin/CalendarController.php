<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\ModelsExtended\Itinerary;
use App\ModelsExtended\ItineraryStatus;
use App\ModelsExtended\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    /**
     * @var Authenticatable|null|User
     */
    private $user;

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $this->user = auth()->user();

        $this->validatedRules(
            [
                'from' => 'required|date_format:Y-m-d',
                'to' => 'required|date_format:Y-m-d|after_or_equal:from',
            ]
        );

        return  $this->getDataQuery()->get()->map->formatTuiArray();
    }

    private function getDataQuery(): Builder
    {
        return Itinerary::query()
            ->when($this->user->isLoggedInAsMasterAccount(), function ( Builder $builder){
                $builder->whereHas( "user.master_sub_account", function (Builder $builder){
                    $builder->where( "master_sub_account.master_account_id", $this->user->masterAccountId() );
                });
            })
            ->where( "itinerary.status_id", ItineraryStatus::Accepted )
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
    }
}
