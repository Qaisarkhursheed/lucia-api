<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings\Cruises;

use App\Http\Controllers\Agent\Itinerary\Bookings\CruiseItemsController;
use App\Http\Middleware\ConvertEmptyStringToNullMiddleware;
use App\ModelsExtended\CruiseCabin;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * @property CruiseCabin $model
 */
class CabinsController extends CruiseItemsController
{
    public function __construct()
    {
        parent::__construct( "cruise_cabin_id" );
        $this->middleware( ConvertEmptyStringToNullMiddleware::class );
    }

    public function fetch()
    {
        return $this->model->presentForDev();
    }

    /**
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function fetchAll()
    {
        return $this->getDataQuery()->get()->map->presentForDev();
    }

    public function getCommonRules()
    {
        return [
            'cabin_category' => 'required|string|max:100',
            'guest_name' => 'nullable|string|max:100',
            'number_of_guests' => 'filled|integer|min:0',
            'bedding_type' => 'required|string|max:100',
            'confirmation_reference' => 'nullable|string|max:100',
        ];
    }

    /**
     * @inheritDoc
     */
    public function store( Request $request )
    {
       return DB::transaction(function () use ( $request ){
           $this->model = $this->getCruise()->cruise_cabins()
               ->create($this->validatedRules( $this->getCommonRules() ));

           return $this->fetch();
       });
    }



    /**
     * @inheritDoc
     */
    public function update( Request $request )
    {
        $this->model->update( $this->validatedRules( $this->getCommonRules() ) );

        return $this->fetch();
    }

    /**
     * @inheritDoc
     */
    public function getDataQuery(): Builder
    {
        return  CruiseCabin::query()
            ->whereHas( "itinerary_cruise.itinerary" , function ( Builder $builder ) {
                $builder->where( "itinerary.user_id", auth()->id() );
            })
            ->where( "itinerary_cruise_id", $this->getCruiseId() );
    }
}
