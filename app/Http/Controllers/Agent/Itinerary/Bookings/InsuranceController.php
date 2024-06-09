<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings;

use App\Http\Controllers\Agent\Itinerary\ItineraryItemsController;
use App\Http\Middleware\ConvertStringsToBooleanMiddleware;
use App\Http\Responses\OkResponse;
use App\ModelsExtended\BookingCategory;
use App\ModelsExtended\ItineraryInsurance;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * @property ItineraryInsurance $model
 */
class InsuranceController extends ItineraryItemsController
{
    public function __construct()
    {
        $this->middleware( ConvertStringsToBooleanMiddleware::class );
        parent::__construct( "insurance_id" );
    }


    public function getDataQuery(): Builder
    {
        return  $this->limitBookingToItineraryOwner( ItineraryInsurance::with("insurance_supplier" , "insurance_pictures"))
            ->where( "itinerary_id", $this->getItineraryId() );
    }

    public function fetch()
    {
        return new OkResponse( $this->model->presentForDev() );
    }

    public function fetchAll()
    {
        return parent::fetchAll()->map->presentForDev();
    }

    public function getCommonRules()
    {
        return [
            'payment' => 'nullable|string',
            'confirmation_reference' => 'nullable|string',
            'company' => 'nullable|string',
            'policy_type' => 'nullable|string',
            'price' => 'filled|numeric',

            'effective_date' => 'required|date_format:Y-m-d',

            'notes' => 'nullable|string|max:3000',
            'cancel_policy' => 'nullable|string|max:3000',
//            'custom_header_title' => 'nullable|string|max:250',

        ];
    }


    /**
     * @inheritDoc
     */
    public function store( Request $request )
    {
        $this->validatedRules( $this->getCommonRulesWithProvider() );

        return DB::transaction(function () use ($request) {
            $this->addNew( $request )->createProvider( $request)
                ->model->refresh();

            return  $this->fetch();
        } );
    }

    /**
     * @param Request $request
     * @return InsuranceController
     */
    private function addNew(Request $request)
    {
        $this->model = $this->getItinerary()->itinerary_insurances()->create(
            [
                'booking_category_id' => BookingCategory::Insurance,
                'sorting_rank' => $this->getItinerary()->getNextSortingRankFor( Carbon::createFromFormat('Y-m-d',$request->input('effective_date')  ) ),

                'price' => $request->input('price'),
                'payment' => $request->input('payment'),
                'company' => $request->input('company'),
                'confirmation_reference' => $request->input('confirmation_reference'),
                'effective_date' => $request->input('effective_date'),
                'policy_type' => $request->input('policy_type'),
                'cancel_policy' => $request->input('cancel_policy'),

                'notes' => $request->input('notes'),
                'custom_header_title' => "Trip Insurance Details",
            ]
        );
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function update( Request $request )
    {
        $this->validatedRules($this->getCommonRulesWithProviderForUpdate());

        $this->model->update(
            [
                'price' => $request->input('price'),
                'payment' => $request->input('payment'),
                'company' => $request->input('company'),
                'confirmation_reference' => $request->input('confirmation_reference'),
                'effective_date' => $request->input('effective_date'),
                'policy_type' => $request->input('policy_type'),
                'cancel_policy' => $request->input('cancel_policy'),

                'notes' => $request->input('notes'),
                'custom_header_title' => "Trip Insurance Details",
            ]
        );

        return $this->updateProvider( $request)->fetch();
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
