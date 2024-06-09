<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings;

use App\Http\Controllers\Agent\Itinerary\ItineraryItemsController;
use App\Http\Middleware\ConvertStringsToBooleanMiddleware;
use App\Http\Responses\OkResponse;
use App\ModelsExtended\BookingCategory;
use App\ModelsExtended\ItineraryOther;
use App\ModelsExtended\UserNote;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * @property ItineraryOther $model
 */
class OtherController extends ItineraryItemsController
{
    public function __construct()
    {
        $this->middleware( ConvertStringsToBooleanMiddleware::class );

        parent::__construct( "other_id" );
    }

    public function getDataQuery(): Builder
    {
        return  $this->limitBookingToItineraryOwner( ItineraryOther::query())
            ->where( "itinerary_id", $this->getItineraryId() );
    }

    public function getCommonRules()
    {
        return [
            'title' => 'required|string|max:150',
            'notes' => 'nullable|string|max:65535',
            'save_to_library' => 'required|boolean',
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
     * @return OtherController
     */
    private function addNew(Request $request)
    {
        $itinerary = $this->getItinerary();
        $this->model = $itinerary->itinerary_others()->create(
            array_merge(
                [
                    'target_date' => $itinerary->start_date,
                    'booking_category_id' => BookingCategory::Other_Notes,
                    'sorting_rank' => $this->getItinerary()->getNextSortingRankFor( $itinerary->start_date ),
                ],
                [
                    'title' => $request->input( 'title' ),
                    'notes' => $request->input( 'notes' ),
                    'save_to_library' => $request->input( 'save_to_library' ),
                ]
            )
        );

        return $this->saveNotesToLibraryIfRequested();
    }

    public function fetch()
    {
        return new OkResponse( $this->model->presentForDev() );
    }

    /**
     * @param Request $request
     * @return OkResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request)
    {
        $this->model->update(  $this->validatedRules($this->getCommonRules()) );
        return $this->saveNotesToLibraryIfRequested()->fetch();
    }

    /**
     * @return $this
     */
    private function saveNotesToLibraryIfRequested(): OtherController
    {
        if( $this->model->save_to_library )
        {
            UserNote::addOrUpdate( $this->model->title, auth()->id(), $this->model->notes );
        }
        return $this;
    }
}
