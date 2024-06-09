<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings;

use App\Http\Controllers\Agent\Itinerary\ItineraryItemsController;
use App\Http\Middleware\ConvertStringsToBooleanMiddleware;
use App\Http\Responses\OkResponse;
use App\ModelsExtended\BookingCategory;
use App\ModelsExtended\ItineraryConcierge;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * @property ItineraryConcierge $model
 */
class ConciergeController extends ItineraryItemsController
{
    public function __construct()
    {
        $this->middleware( ConvertStringsToBooleanMiddleware::class , [
            "only" => [
                "store", "update"
            ]
        ]);
        parent::__construct( "concierge_id" );
    }

    public function fetch()
    {
        return new OkResponse( $this->model->presentForDev() );
    }

    public function fetchAll()
    {
        return parent::fetchAll()->map->presentForDev();
    }

    /**
     * Get data query used to build this page
     * @return Builder
     */
    public function getDataQuery(): Builder
    {
        return  $this->limitBookingToItineraryOwner( ItineraryConcierge::with("concierge_supplier", "concierge_pictures" ))
            ->where( "itinerary_id", $this->getItineraryId() );
    }

    /**
     * @return string[]
     */
    public function getCommonRules()
    {
        return [
            'service_type' => 'nullable|string',
            'payment' => 'nullable|string',
            'confirmation_reference' => 'nullable|string',
            'confirmed_for' => 'nullable|string',
            'confirmed_by' => 'nullable|string',
            'price' => 'filled|numeric',

            'start_time' => 'filled|date_format:h\:i\ A',
            'end_time' => 'filled|date_format:h\:i\ A',

            'start_day' => 'required|date_format:Y-m-d',
            'end_day' => 'filled|date_format:Y-m-d|after_or_equal:start_day',

            'notes' => 'nullable|string|max:3000',
            'cancel_policy' => 'nullable|string|max:3000',
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
            $this->addNew( $request )->createProvider( $request )
                ->model->refresh();


            return  $this->fetch();
        } );
    }

    /**
     * @param Request $request
     * @return ConciergeController
     */
    private function addNew(Request $request)
    {
        $dateRange = $this->fetchParsedDateRange($request);
        $this->model = $this->getItinerary()->itinerary_concierges()->create(
            [
                'booking_category_id' => BookingCategory::Concierge,
                'sorting_rank' => $this->getItinerary()->getNextSortingRankFor( Carbon::createFromFormat('Y-m-d',$request->input('start_day')  ) ),

                'service_type'  => $request->input('service_type'),
                'price'  => $request->input('price'),
                'payment'  => $request->input('payment'),
                'confirmation_reference'  => $request->input('confirmation_reference'),
                'confirmed_for'  => $request->input('confirmed_for'),
                'confirmed_by'  => $request->input('confirmed_by'),

                'start_datetime' => $dateRange["start_datetime"],
                'end_datetime' => $dateRange["end_datetime"],

                'cancel_policy' => $request->input('cancel_policy'),
                'notes' => $request->input('notes'),
                'custom_header_title' => $request->input('custom_header_title'),
            ]
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function update( Request $request )
    {
        $this->validatedRules( $this->getCommonRulesWithProviderForUpdate() );

        $dateRange = $this->fetchParsedDateRange($request);

        $this->model->update(
            [
                'service_type'  => $request->input('service_type'),
                'price'  => $request->input('price'),
                'payment'  => $request->input('payment'),
                'confirmation_reference'  => $request->input('confirmation_reference'),
                'confirmed_for'  => $request->input('confirmed_for'),
                'confirmed_by'  => $request->input('confirmed_by'),

                'start_datetime' => $dateRange["start_datetime"],
                'end_datetime' => $dateRange["end_datetime"],

                'cancel_policy' => $request->input('cancel_policy'),
                'notes' => $request->input('notes'),
                'custom_header_title' => $request->input('custom_header_title'),
            ]
        );

        return $this->updateProvider( $request  )->fetch();
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
