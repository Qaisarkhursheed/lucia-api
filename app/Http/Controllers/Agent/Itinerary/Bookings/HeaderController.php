<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings;

use App\Http\Controllers\Agent\Itinerary\ItineraryItemsController;
use App\Http\Responses\OkResponse;
use App\ModelsExtended\BookingCategory;
use App\ModelsExtended\ItineraryHeader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * @property ItineraryHeader $model
 */
class HeaderController extends ItineraryItemsController
{
    public function __construct()
    {
        parent::__construct( "header_id" );
    }

    public function getDataQuery(): Builder
    {
        return  $this->limitBookingToItineraryOwner( ItineraryHeader::query())
            ->where( "itinerary_id", $this->getItineraryId() );
    }

    public function getCommonRules()
    {
        return [
            'custom_header_title' => 'filled|string|max:250',
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
     * @return HeaderController
     */
    private function addNew(Request $request): HeaderController
    {
        $itinerary = $this->getItinerary();
        $this->model = $itinerary->itinerary_headers()->create(
            [
                'target_date' => $request->input('target_date', $itinerary->start_date),
                'booking_category_id' => BookingCategory::Header,
                'sorting_rank' => $this->getItinerary()->getNextSortingRankFor( $itinerary->start_date ),
                'custom_header_title' => $request->input('custom_header_title'),
            ]
        );

        return $this;
    }

    public function fetch()
    {
        return new OkResponse( $this->model->formatForSharing() );
    }

    /**
     * @param Request $request
     * @return OkResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request)
    {
        $this->model->update(  $this->validatedRules($this->getCommonRules()) );
        return $this->fetch();
    }
}
