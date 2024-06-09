<?php

namespace App\Http\Controllers\Admin\Providers;

use App\Http\Controllers\Enhancers\CRUDEnabledController;
use App\Http\Controllers\Enhancers\IYajraEloquentResultProcessorInterface;
use App\Http\Controllers\Enhancers\YajraPaginableTraitController;
use App\Http\Middleware\ConvertStringsToBooleanMiddleware;
use App\Http\Responses\OkResponse;
use App\ModelsExtended\BookingCategory;
use App\ModelsExtended\ServiceSupplier;
use App\ModelsExtended\User;
use App\Rules\PhoneNumberValidationRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Nette\NotImplementedException;
use Rap2hpoutre\FastExcel\FastExcel;

/**
 * @property ServiceSupplier $model
 */
class SuppliersController  extends CRUDEnabledController implements IYajraEloquentResultProcessorInterface
{
    use YajraPaginableTraitController;

    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable|null |User
     */
    protected ?\Illuminate\Contracts\Auth\Authenticatable $user;

    public function __construct()
    {
        $this->middleware( ConvertStringsToBooleanMiddleware::class );

        $this->user = auth()->user();

        parent::__construct( "supplier_id", "service_suppliers.id" );
    }

    public function getCommonRules()
    {
        return [
            'booking_category_id' => 'required|exists:booking_category,id',

            'name' => 'required|string|max:150',
            'address' => 'nullable|string|max:300',
            'phone' => [ 'nullable', 'max:30', new PhoneNumberValidationRule() ],
            'website' => 'nullable|string|max:150',
            'email' => 'nullable|email|max:150',
            'description' => 'nullable|string|max:10000',
            'is_globally_accessible' => 'nullable|boolean',

//            'supplier_image' => 'filled|image|max:20000',    // 20MB

            'images' => 'nullable|array|max:6',    // 20MB
            'images.*' => 'image|max:20000',    // 20MB
        ];
    }

    public function fetch()
    {
        return $this->model->presentForDev();
    }

    /**
     * @inheritDoc
     * @return array
     */
    public function store(Request $request)
    {
        $this->validatedRules( $this->getCommonRules() );

        // at the moment, service supplier info is just complemented.
        // You can't really change it after creating it.

        $this->model =  ServiceSupplier::createOrUpdateFromRequest(
             $request->input( 'name' ),
            $request->input( 'address' ),
            $request->input( 'phone' ),
             $request->input( 'website' ),
             $request->input( 'email' ),
            $request->input( 'booking_category_id' ),
             auth()->id(),
            $request->has( 'is_globally_accessible' ) ?
                $request->input( 'is_globally_accessible' ): false
        );

        $this->model->updateDescription( $request->input( 'description' ) );

//        $supplier->image_url = $request->hasFile('supplier_image')?
//                $this->storeRequestImageUrl( ServiceSupplier::generateImageUrlFullPath() )
//            : $supplier->image_url;
//        $supplier->updateQuietly();

        return $this->storeImagesIfUploaded($request, $this->model )->fetch();
    }

    /**
     * Update loaded resource / model
     *
     * @param Request $request
     * @throws NotImplementedException
     * @throws ValidationException
     */
    public function update( Request $request ){
        $this->validatedRules($this->getCommonRules());

        $this->storeImagesIfUploaded($request, $this->model );

//        // Store image file if passed
//        if( $request->hasFile('supplier_image')  )
//        {
//            // delete if it exists
//            if( Storage::cloud()->exists(  $this->model->getImageUrlStorageRelativePath() ) )
//                Storage::cloud()->delete(  $this->model->getImageUrlStorageRelativePath() );
//
//            // We are generating new url to prevent caching
////            $this->model->image_url = $this->model->forceGetImageUrlPathOnCloud();
//            $this->model->image_url = ServiceSupplier::generateImageUrlFullPath();
//
//            // store the real full path
//            $this->storeRequestImageUrl( $this->model->image_url );
//        }

        // update the whole parameters
        $this->model->update( $request->only( [
            'name',
            'address',
            'phone',
            'website',
            'email',
            'booking_category_id',
            'is_globally_accessible',
            'description',
        ] ) );

        return $this->fetch();
    }

    public function delete()
    {
        Storage::cloud()->deleteDirectory($this->model->getFolderStorageRelativePath());
        return parent::delete();
    }

//    /**
//     * Accept relative image url and full image url.
//     * Returns the image url passed in.
//     * @param string $image_url
//     * @return string
//     */
//    public function storeRequestImageUrl(string $image_url): string
//    {
//        return self::storeImageUrl( $image_url, \request()->file('supplier_image')->getContent() );
//    }

//    /**
//     * Accept relative image url and full image url.
//     * Returns the image url passed in.
//     * @param string $image_url
//     * @param mixed $file_content
//     * @return string
//     */
//    public static function storeImageUrl( string $image_url, $file_content ): string
//    {
//        $relative_image_url = Str::of( $image_url )->contains( "://" ) ? ModelBase::getStorageRelativePath( $image_url ) : $image_url;
//
//        Storage::cloud()->put( $relative_image_url, $file_content );
//
//        return $image_url;
//    }


    /**
     * @return array|Builder[]|\Illuminate\Database\Eloquent\Collection|JsonResponse
     * @throws ValidationException
     */
    public function fetchAll()
    {
        $this->validatedRules( [
            'booking_category_id' => 'nullable|exists:booking_category,id',
        ] );

        return $this->paginateYajra( $this );
    }

    /**
     * @param Collection $data
     * @return Collection
     * @throws \Exception
     */
    private function preprocess( Collection $data)
    {
        try {
            $data = $data->map( function ( $item, $key ){
                $item = array_values($item);

                $category = BookingCategory::query()->where( "description", $item[1] )->firstOrFail();

                $email = $item[3];

                if( trim( $email ) && ! $this->isValidEmailArray( [ $email ] ) )
                    throw new InvalidArgumentException(
                        sprintf( 'Line with supplier name [ %s ] contains invalid email address [ %s ]',
                            $item[0], $email
                        )
                    );

                $collect = collect(
                    [
                        'name' => $item[0],
                        'address' => $item[2],
                        'phone' => $this->cleanUpPhoneNumber( $item[4] ),
                        'website' => $item[5],
                        'email' => $item[3],
                        'description' => $item[7],
                        'booking_category_id' => $category->id,
                        'created_by_id' => auth()->id(),
                        'is_globally_accessible' => true
                    ]
                );
//                if( $item[6] && strlen( $item[6] ) > 5 ) $collect->put( 'image_url', $item[6] );

                return $collect->toArray();
            });
        }
        catch (\InvalidArgumentException $e) {
            throw $e;
        }
        catch (\Exception $e) {
            Log::error( $e->getMessage(), $e->getTrace() );
            throw new \Exception( "Invalid Document Uploaded. Please, download the template and make sure all entries are correct including Categories!" ) ;
        }

        return $data;
    }

    /**
     * @throws \Exception
     */
    public function import(Request $request, FastExcel $fastExcel )
    {
        $this->validatedRules( [
            'File__Import' => 'required|file|min:0|max:10000',  // 10MB
        ] );

        $File__Import =  $request->file( 'File__Import' );

        $data = $fastExcel->import( $File__Import->getRealPath() );

        if( $data->count() === 0 ) throw new \Exception("No rows found!");

        $data = $this->preprocess( $data );

        DB::transaction( function () use ($data){
            $data->each( function ( array $items ){
                ServiceSupplier::updateOrCreate(
                    Arr::only(  $items, [ 'name'  , 'booking_category_id' ] ),
                    $items
                );
            });
        });

        return new OkResponse( [
            "records_imported" => $data->count()
        ] );
    }

    /**
     * @inheritDoc
     */
    protected function getQuery(): Builder
    {
        return ServiceSupplier::with( 'booking_category', 'user:id,name' )
            ->select(
                'id',
                'name',
                'address',
                'phone',
                'website',
                'description',
                'email',
                'booking_category_id',
                'created_by_id',
                'is_globally_accessible',
                'created_at',
            );
    }

    /**
     * @param Builder $query
     * @return Builder|mixed
     */
    protected function filterQuery(Builder $query)
    {
        return $query->when($this->search, function (Builder $builder) {
            $search = $this->search;
            $builder->where("service_suppliers.name", 'like', "%$search%");
        })->when( \request( 'booking_category_id' ), function (Builder $builder) {
            $builder->where("service_suppliers.booking_category_id",  \request( 'booking_category_id' ) );
        });
    }

    /**
     * @inheritDoc
     */
    public function getDataQuery(): Builder
    {
        return $this->getQuery();
    }

    /**
     * @inheritDoc
     */
    public function processYajraEloquentResult($result): array
    {
        return $result->map->presentForDev()
            ->all();
    }

    /**
     * @param string|null $entry
     * @return string|null
     */
    public static function cleanUpPhoneNumber(?string $entry): ?string
    {
        if( !$entry ) return $entry;
        return Str::start( str_replace( [ ' ', ')', '(', '.', ']', '[' ], '', $entry ) , "+" );
    }

    /**
     * @param Request $request
     * @param ServiceSupplier $supplier
     * @return $this
     */
    private function storeImagesIfUploaded(Request $request, ServiceSupplier $supplier): SuppliersController
    {
        if( $request->hasFile('images' ) )
        {
            foreach ( $request->file('images') as  $image )
            {
                // for now, i will let you add as much as you want
                $supplier->addPicture($image);
            }
        }

        return $this;
    }
}
