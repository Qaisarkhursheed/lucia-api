<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings;

use App\Http\Controllers\Agent\Itinerary\ItineraryItemsController;
use App\ModelsExtended\ItineraryInsurance;

abstract class InsuranceItemsController extends ItineraryItemsController
{
    public function __construct(string $param_name)
    {
        parent::__construct( $param_name );
    }

    /**
     * @return int|object|string|null
     */
    protected function getInsuranceId()
    {
        return \request()->route( 'insurance_id' );
    }

    /**
     * @return ItineraryInsurance
     */
    protected function getInsurance()
    {
        return ItineraryInsurance::find( $this->getInsuranceId() );
    }
}
