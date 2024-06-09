<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings;

use App\Http\Controllers\Agent\Itinerary\ItineraryItemsController;
use App\Http\Responses\OkResponse;
use App\ModelsExtended\BookingCategory;
use App\ModelsExtended\ItineraryDivider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * @property ItineraryDivider $model
 */
class DividerController extends ItineraryItemsController
{
    public function __construct()
    {
        parent::__construct( "divider_id" );
    }

    public function getDataQuery(): Builder
    {
        return  $this->limitBookingToItineraryOwner( ItineraryDivider::query())
            ->where( "itinerary_id", $this->getItineraryId() );
    }

    public function getCommonRules()
    {
        return [
            'target_date' => 'filled|date_format:Y-m-d',
        ];
    }

    /**
     * @inheritDoc
     */
    public function store( Request $request )
    {
        $this->validatedRules(  $this->getCommonRules() ) ;
        return $this->addNew($request)->fetch();
    }

    /**
     * @param Request $request
     * @return DividerController
     */
    private function addNew(Request $request): DividerController
    {
        $itinerary = $this->getItinerary();
        $this->model = $itinerary->itinerary_dividers()->create(
            [
                'target_date' => $request->input('target_date', $itinerary->start_date),
                'booking_category_id' => BookingCategory::Divider,
                'sorting_rank' => $this->getItinerary()->getNextSortingRankFor( $itinerary->start_date ),
            ]
        );

        return $this;
    }

    public function fetch()
    {
        return new OkResponse( $this->model->formatForSharing() );
    }
}
