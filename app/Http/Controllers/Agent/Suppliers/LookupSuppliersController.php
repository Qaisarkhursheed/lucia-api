<?php

namespace App\Http\Controllers\Agent\Suppliers;

use App\Http\Controllers\Controller;
use App\Http\Middleware\ConvertStringsToBooleanMiddleware;
use App\ModelsExtended\BookingCategory;
use App\ModelsExtended\ServiceSupplier;
use App\Repositories\Maps\GoogleMaps\GooglePlaceIdAnalyzer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class LookupSuppliersController extends Controller
{
    public function __construct()
    {
        $this->middleware( ConvertStringsToBooleanMiddleware::class );
    }

    public function supplierLookup ()
    {
        $this->validatedRules([
            'search' => 'nullable|string|max:150',
            'detailed' => 'nullable|boolean',
            'booking_category_id' => 'required|exists:booking_category,id',
        ]);

        return $this->transform(
             $this->getQuery(  request("booking_category_id" ) )
            ->when( request( 'search' ), function ( Builder $builder ){
                $builder->where( "name" , 'like', sprintf( "%%%s%%" , request("search" ) ) );
            } )
            ->get(),
            request('detailed')
          );
    }

    public function shipLookup ()
    {
        $this->validatedRules([
            'search' => 'nullable|string|max:150',
            'detailed' => 'nullable|boolean',
        ]);

        return $this->transform(
            $this->getQuery(  BookingCategory::Cruise )
                ->when( request( 'search' ), function ( Builder $builder ){
                    $builder->whereHas( "service_ships" , function ( Builder $builder ){
                        $builder->where( "service_ships.name" , 'like', sprintf( "%%%s%%" , request("search" ) ) );
                    }  );
                } )
                ->get(),
            request('detailed')
        );
    }

    private function getQuery( int $booking_category_id )
    {
        return ServiceSupplier::with( 'service_ships', 'ship_ports' )
            ->where( "booking_category_id" , $booking_category_id );
    }

    /**
     * @param Collection $collection
     * @param $detailed
     * @return Collection
     */
    private function transform( Collection $collection, $detailed = false )
    {
        return $collection->map(function (ServiceSupplier $item) use( $detailed ){
            return $item->presentForDev();
//            $value = [
//                    'name' => $item->name,
//                    'address' => $item->address,
//                    'phone' => $item->phone,
//                    'website' => $item->website,
//                    'email' => $item->email,
//                    'image_url' => $item->image_url,
//                    "ships"=> $item->service_ships->pluck('name' ),
//                ];
//                if( $detailed )
//                {
//                    $value = array_merge( $value , [
//                        'description' => $item->description,
//                        'image_url' => $item->image_url,
//                        "ports"=> $item->ship_ports
//                            ->map->only(
//                                [
//                                    'name',
//                                    'description',
//                                    'latitude',
//                                    'longitude',
//                                ]
//                            ),
//                    ] );
//                }else{
//                    $value["ports"] = $item->ship_ports->pluck('name');
//                }
//                return $value;
            } );
    }

    /**
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     * @throws \SKAgarwal\GoogleApi\Exceptions\GooglePlacesApiException
     */
    public function googlePlaceIdHotelSearch()
    {
        $this->validatedRules([
            'place_id' => 'required|string|max:250',
            'detailed' => 'nullable|boolean',
        ]);

        return $this->transform(
            collect()->push(
                ServiceSupplier::createOrUpdate( new GooglePlaceIdAnalyzer(request( 'place_id' ) ), BookingCategory::Hotel )
            ),
            request('detailed')
        )->first();
    }
}
