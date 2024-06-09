<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings;

use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Agent\Itinerary\ItineraryItemsController;
use App\Http\Middleware\ConvertStringsToBooleanMiddleware;
use App\Http\Responses\OkResponse;
use App\ModelsExtended\BookingCategory;
use App\ModelsExtended\ItineraryTour;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * @property ItineraryTour $model
 */
class TourController extends ItineraryItemsController
{
    public function __construct()
    {
        $this->middleware( ConvertStringsToBooleanMiddleware::class );

        parent::__construct( "tour_id" );
    }

    public function fetch()
    {
        return new OkResponse( $this->model->presentForDev() );
    }

    public function fetchAll()
    {
        return parent::fetchAll()->map->presentForDev();
    }

    public function getDataQuery(): Builder
    {
        return  $this->limitBookingToItineraryOwner( ItineraryTour::with("tour_supplier" , "tour_pictures" ))
            ->where( "itinerary_id", $this->getItineraryId() );
    }

    public function getCommonRules()
    {
        return [
            'payment' => 'nullable|string',
            'confirmation_reference' => 'nullable|string',
            'meeting_point' => 'nullable|string',
            'confirmed_by' => 'nullable|string',
            'price' => 'filled|numeric',

            'start_day' => 'required|date_format:Y-m-d',
            'end_day' => 'required_with:start_day,end_day,start_time,end_time|date_format:Y-m-d|after_or_equal:start_day',

            'start_time' => 'filled|date_format:h\:i\ A',
            'end_time' => 'filled|date_format:h\:i\ A',

            'notes' => 'nullable|string|max:3000',
            'description' => 'nullable|string|max:3000',
            'custom_header_title' => 'nullable|string|max:250',

        ];
    }

    /**
     * @inheritDoc
     */
    public function store( Request $request )
    {
        $this->validatedRules( $this->getCommonRulesWithOptionalProvider() );

        return DB::transaction(function () use ($request) {
             $this->addNew( $request )
                ->createProvider( $request)
                 ->model->refresh();

            return  $this->fetch();
        } );
    }

    /**
     * @param Request $request
     * @return TourController
     */
    private function addNew(Request $request)
    {
        $dateRange = $this->fetchParsedDateRange($request);

        $this->model = $this->getItinerary()->itinerary_tours()->create(
            [
                'booking_category_id' => BookingCategory::Tour_Activity,
                'sorting_rank' => $this->getItinerary()->getNextSortingRankFor( Carbon::createFromFormat('Y-m-d',$request->input('start_day')  ) ),

                'price'  => $request->input('price'),
                'payment'  => $request->input('payment'),
                'confirmation_reference'  => $request->input('confirmation_reference'),
                'meeting_point'  => $request->input('meeting_point'),
                'confirmed_by'  => $request->input('confirmed_by'),
                'description'  => $request->input('description'),

                'start_datetime' => $dateRange["start_datetime"],
                'end_datetime' => $dateRange["end_datetime"],

                'notes' => $request->input('notes'),
                'custom_header_title' => $request->input('custom_header_title'),

            ]
        );

        return  $this;
    }

    /**
     * @inheritDoc
     */
    public function update( Request $request )
    {
        $this->validatedRules($this->getCommonRulesWithProviderForUpdate());

        $dateRange = $this->fetchParsedDateRange($request);

        $this->model->update( [

            'price'  => $request->input('price'),
            'payment'  => $request->input('payment'),
            'confirmation_reference'  => $request->input('confirmation_reference'),
            'meeting_point'  => $request->input('meeting_point'),
            'confirmed_by'  => $request->input('confirmed_by'),
            'description'  => $request->input('description'),

            'notes' => $request->input('notes'),
            'custom_header_title' => $request->input('custom_header_title'),

            'start_datetime' => $dateRange["start_datetime"],
            'end_datetime' => $dateRange["end_datetime"],
        ] );

        return $this->updateProvider( $request )->fetch();
    }

    /**
     * @inheritDoc
     */
    public function delete()
    {
        Storage::cloud()->deleteDirectory($this->model->getFolderStorageRelativePath());
        return parent::delete();
    }
}
