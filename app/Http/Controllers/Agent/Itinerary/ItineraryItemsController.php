<?php

namespace App\Http\Controllers\Agent\Itinerary;

use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\ISupplierCompatible;
use App\Http\Controllers\Enhancers\CRUDEnabledController;
use App\ModelsExtended\Interfaces\ICanCreateServiceProviderInterface;
use App\ModelsExtended\Interfaces\IDoNotCreateGlobalSupplierInterface;
use App\ModelsExtended\Itinerary;
use App\ModelsExtended\ModelBase;
use App\ModelsExtended\PassengerType;
use App\ModelsExtended\ServiceSupplier;
use App\Rules\PhoneNumberValidationRule;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

/**
 * @property ICanCreateServiceProviderInterface $model
 */
abstract class ItineraryItemsController extends CRUDEnabledController
{
    // REFER as static:: NOT self:: if overridden
    // 20 MB
    const MAX_PICTURE_SIZE_IN_BYTE = 20 * 1000 * 1000 ;

    // Default numbers of pictures
    protected int $MAXIMUM_PICTURES = 2;

    /**
     * @var Builder|\Illuminate\Database\Eloquent\Model|mixed|Itinerary
     */
    private $itinerary;

    public function __construct(string $param_name, string $recordIdentifier = "id")
    {
        parent::__construct( $param_name, $recordIdentifier );
    }

    /**
     * @return int|object|string|null
     */
    protected function getItineraryId()
    {
        return \request()->route( 'itinerary_id' );
    }

    /**
     * @return Itinerary|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    protected function getItinerary()
    {
        // enable caching
        if( $this->itinerary ) return $this->itinerary;

        $this->itinerary = Itinerary::query()
            ->where("id", $this->getItineraryId())

            // limit to owner if you are operating as agent
            ->when( auth()->user()->isLoggedInAsTravelAgent(), function ( Builder $builder ){
                $builder->where("user_id", auth()->id());
            } )

            ->firstOrFail();

        return $this->itinerary;
    }

    /**
     * Add a filter to booking assuming booking is directly related
     * with 'itinerary' as a relationship
     *
     * @param Builder $builder
     * @return Builder
     */
    protected function limitBookingToItineraryOwner( Builder $builder): Builder
    {
        return $builder->where(function ( Builder $builder ){
            // limit to owner if you are operating as agent
            $builder->when( auth()->user()->isLoggedInAsTravelAgent(), function ( Builder $builder ){
                $builder->whereHas( "itinerary" , function ( Builder $builder ) {
                    $builder->where( "itinerary.user_id", auth()->id() );
                });
            } );
        });
    }

    /**
     * Details needed to save a provider
     * @return array
     */
    protected function getProviderRule()
    {
        return [
            'provider_name' => 'required|string|max:150',
            'provider_address' => 'nullable|string|max:300',
            'provider_phone' => [ 'nullable', 'max:30' ],
            'provider_website' => 'nullable|string|max:300',
            'provider_email' => 'nullable|email|max:150',
            'save_to_library' => 'required|boolean',
        ];
    }

    /**
     * @return array
     */
    public function getCommonRulesWithProvider()
    {
        return array_merge( $this->getCommonRules(), $this->getProviderRule() );
    }

    /**
     * @return array
     */
    public function getCommonRulesWithOptionalProvider()
    {
        return array_merge( $this->getCommonRules(), $this->getProviderRule(), [
            'provider_name' => 'nullable|string|max:150'
        ] );
    }

    public function getCommonRulesWithProviderForUpdate()
    {
        return Arr::except( $this->getCommonRulesWithProvider() , [ 'save_to_library', 'provider_name' ]  );
    }

    /**
     * @param Request $request
     * @return $this
     */
    protected function createProvider( Request $request)
    {
        if( ! $request->input( 'provider_name' ) ) return $this;

        $this->throwIfModelCanNotCreateSupplier()
        ->model->{ $this->model->getSupplierRelationshipAttributeName() }()->create(
            [
                'name' => $request->input( 'provider_name' ),
                'address' => $request->input( 'provider_address' ),
                'phone' => $request->input( 'provider_phone' ),
                'website' => $request->input( 'provider_website' ),
                'email' => $request->input( 'provider_email' ),
                'save_to_library' => $request->input( 'save_to_library' ),
                'description' => $request->input('notes'),
            ]
        );

        if( $request->input( 'save_to_library' )
//            && !( $this->model instanceof IDoNotCreateGlobalSupplierInterface)
        )
        {
            // $this->createGlobalSupplier( $request );
            ServiceSupplier::createOrUpdateFromRequest($request->input('provider_name'),
                $request->input( 'provider_address' ),
                $request->input( 'provider_phone' ), $request->input( 'provider_website' ),
                $request->input( 'provider_email' ),
                $this->model->getBookingCategoryId(), auth()->id(), false
            )->updateDescription( $request->input('notes') )
                ->saved_suppliers()
                ->updateOrCreate([
                'user_id' => auth()->id()
            ]);
        }

        return $this;
    }

    /**
     * Throws exception if model doesn't implement ICanCreateServiceProviderInterface
     * @return $this
     */
    private function throwIfModelCanNotCreateSupplier()
    {
        if( ! ( $this->model instanceof ICanCreateServiceProviderInterface ) )
            throw new \InvalidArgumentException( "Please, implement ICanCreateServiceProviderInterface in your model!" );
        return $this;
    }

//    /**
//     * Create a supplier that is globally accessible
//     * @param Request $request
//     * @return ServiceSupplier
//     */
//    private function createGlobalSupplier(Request $request)
//    {
//        // only allow creation
//        return ServiceSupplier::getSupplier(
//            $request->input( 'provider_name' ) ,
//            $this->model->getBookingCategoryId()
//        ) ?? ServiceSupplier::create([
//            'name' => $request->input( 'provider_name' ),
//            'address' => $request->input( 'provider_address' ),
//            'phone' => $request->input( 'provider_phone' ),
//            'website' => $request->input( 'provider_website' ),
//            'email' => $request->input( 'provider_email' ),
//            'booking_category_id' => $this->model->getBookingCategoryId(),
//            'created_by_id' => auth()->id(),
//            'is_globally_accessible' => false
//        ]);
//    }

    /**
     * Update the supplier [Linked or Direct] on this model
     * @param Request $request
     * @return $this
     */
    public function updateProvider(Request $request )
    {
        $supplier = $this->throwIfModelCanNotCreateSupplier()
                    ->model->getSupplierAttribute();

        // supplier was never created
        if( !$supplier ) return $this;

//        $supplier = $this->model->getSupplierAttribute();
        $supplier->address = $request->input( 'provider_address' , $supplier->address);
        $supplier->phone = $request->input( 'provider_phone' , $supplier->phone);
        $supplier->website = $request->input( 'provider_website' , $supplier->website);
        $supplier->email = $request->input( 'provider_email' , $supplier->email);
        $supplier->description = $request->input('notes', $supplier->description );
        $supplier->update();

//        if( !$provider_model ) return $this;

////        $provider_model->name = $request->input( 'provider_name' , $provider_model->name);
//        $provider_model->address = $request->input( 'provider_address' , $provider_model->address);
//        $provider_model->phone = $request->input( 'provider_phone' , $provider_model->phone);
//        $provider_model->website = $request->input( 'provider_website' , $provider_model->website);
//        $provider_model->email = $request->input( 'provider_email' , $provider_model->email);
//
//        $provider_model->update();

        return $this;
    }

    /**
     * Call the method handler to handle images uploaded
     * @param Request $request
     * @param IBookingCanStoreImageControllerInterface $storeHandlerController
     * @param ModelBase $baseModel
     * @param string $modelRelationName
     * @return mixed
     * @throws ValidationException
     */
    public function invokeImageStoreRequest(
        Request $request,
        IBookingCanStoreImageControllerInterface $storeHandlerController,
        ModelBase $baseModel,
        string $modelRelationName
        )
    {
        $this->validatedRules([
            'image_url' => 'required|array|max:' . $this->MAXIMUM_PICTURES,
//            'image_url' => 'required|image|max:20000',    // 20MB
        ]);

        return $this->runInALock( sprintf( 'uploading-%s-%d', $modelRelationName, $baseModel->getKey() )  ,
            function ( ) use( $request, $storeHandlerController, $baseModel, $modelRelationName ){

                if( $baseModel->{$modelRelationName}->count() === $this->MAXIMUM_PICTURES )
                    throw new \Exception( "You have reached the maximum upload limit of " . $this->MAXIMUM_PICTURES );

                if( $baseModel->{$modelRelationName}->count() + count( $request->file( 'image_url' ) ) > $this->MAXIMUM_PICTURES )
                    throw new \Exception( "Your upload will go beyond the maximum upload limit of " . $this->MAXIMUM_PICTURES );

                foreach ($request->file('image_url') as $image) {
                    if ($image->getSize() > static::MAX_PICTURE_SIZE_IN_BYTE) // 20 MB
                        throw new \Exception("Image size is too large. Maximum of 20 MB");

                    $storeHandlerController->storeImage($image, $baseModel);
                }

                return $this->fetchAll();
            });
    }

    /**
     * Call the method handler to handle images uploaded
     * @param Request $request
     * @param ISupplierCompatible $supplierCompatible
     * @return mixed
     * @throws ValidationException
     */
    public function invokeImageStoreForSupplierCompatible(
        Request $request,
        ISupplierCompatible $supplierCompatible
    )
    {
        $this->validatedRules([
            'image_url' => 'required|array|max:' . $this->MAXIMUM_PICTURES,
            'image_url.*' => 'image|max:20000',    // 20MB
        ]);

        return $this->runInALock( sprintf( 'uploading-%s-%d', $supplierCompatible->getName(), $supplierCompatible->getId() )  ,
            function ( ) use( $request, $supplierCompatible ){

                if( $supplierCompatible->getPictures()->count() === $this->MAXIMUM_PICTURES )
                    throw new \Exception( "You have reached the maximum upload limit of " . $this->MAXIMUM_PICTURES );

                if( $supplierCompatible->getPictures()->count() + count( $request->file( 'image_url' ) ) > $this->MAXIMUM_PICTURES )
                    throw new \Exception( "Your upload will go beyond the maximum upload limit of " . $this->MAXIMUM_PICTURES );

                foreach ($request->file('image_url') as $image) {
//                    if ($image->getSize() > static::MAX_PICTURE_SIZE_IN_BYTE) // 20 MB
//                        throw new \Exception("Image size is too large. Maximum of 20 MB");
                    $supplierCompatible->addPicture($image);
                }

                return $this->fetchAll();
            });
    }

    /**
     * @param array $passengers
     * @throws \Exception
     */
    public static function validatePassengers( array $passengers )
    {
        if( ! count( $passengers ) ) return;

        $passenger_types = PassengerType::all()->pluck( 'id' )->toArray();

        foreach ( $passengers as $passenger )
        {
            $v = (object) $passenger;
            if( ! optional( $v )->name  ) throw new \Exception( 'Please, enter valid passenger name' );
            if(
                array_key_exists( 'passenger_type_id', $passenger )  &&
                ! in_array( $v->passenger_type_id , $passenger_types )
            ) throw new \Exception( 'Please, enter valid passenger type' );
        }
    }

    /**
     * Only parameter required here is StartDay
     * Values contains carbon or null UTC
     *
     * It returns  ["start_datetime" => $start_datetime,"end_datetime" => $end_datetime ]
     *
     * @param Request $request
     * @param string $startDayKey
     * @param string $startTimeKey
     * @param string $endDayKey
     * @param string $endTimeKey
     * @return array
     */
    protected function fetchParsedDateRange(Request $request,
                                            string $startDayKey = 'start_day' , string $startTimeKey = 'start_time',
                                            string $endDayKey = 'end_day' , string $endTimeKey = 'end_time'
    ): array
    {
        $start_day = Carbon::createFromFormat( 'Y-m-d', $request->input( $startDayKey )  )->setTime(0,0);
        $start_time =  $request->input( $startTimeKey) ? Carbon::createFromTimeString( $request->input( $startTimeKey) ) : null ;

        $start_datetime = $start_time ?
            $start_day->clone()->setTimeFrom($start_time)->fromPreferredTimezoneToAppTimezone()
            : $start_day->clone()->fromPreferredTimezoneToAppTimezone();


        $end_day = $request->input( $endDayKey ) ? Carbon::createFromFormat( 'Y-m-d', $request->input( $endDayKey )  )->setTime(0,0) : null;
        $end_time =  $request->input( $endTimeKey ) ? Carbon::createFromTimeString( $request->input( $endTimeKey ) ) : null ;

        $end_datetime = null;

        if( $end_day )
        {
            $end_datetime = $end_time ?
                $end_day->clone()->setTimeFrom($end_time)->fromPreferredTimezoneToAppTimezone()
                : $end_day->clone()->fromPreferredTimezoneToAppTimezone();
        }

        return [
            "start_datetime" => $start_datetime,
            "end_datetime" => $end_datetime,
        ];
    }
}
