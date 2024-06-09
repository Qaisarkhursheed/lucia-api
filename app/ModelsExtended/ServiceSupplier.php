<?php

namespace App\ModelsExtended;

use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\ISupplierCompatible;
use App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier\SupplierCompatibleTrait;
use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;
use App\ModelsExtended\Interfaces\IGlobalSearchableInterface;
use App\ModelsExtended\Interfaces\IHasFolderStoragePathModelInterface;
use App\Repositories\Maps\GoogleMaps\GooglePlaceIdAnalyzer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @property Collection|ServiceSuppliersPicture[] $service_suppliers_pictures
 * @property User $user
 */
class ServiceSupplier extends \App\Models\ServiceSupplier
    implements IDeveloperPresentationInterface, IGlobalSearchableInterface, IHasFolderStoragePathModelInterface, ISupplierCompatible
{
    use HasFactory, SupplierCompatibleTrait;

    public function service_suppliers_pictures()
    {
        return $this->hasMany(ServiceSuppliersPicture::class, 'service_suppliers_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * @param GooglePlaceIdAnalyzer $result
     * @param int $booking_category_id
     * @return ServiceSupplier|Builder|\Illuminate\Database\Eloquent\Model|object
     */
    public static function createOrUpdate(GooglePlaceIdAnalyzer $result, int $booking_category_id )
    {
        $supplier = self::getSupplier( $result->name, $booking_category_id );
        if( !$supplier )
        {
            return self::create([
                'name' => $result->name,
                'address' => $result->formatted_address,
                'phone' => $result->international_phone_number,
                'website' => $result->website,
                'email' => null,
                'booking_category_id' => $booking_category_id,
                'created_by_id' => User::DEFAULT_ADMIN, // using default admin here to prevent dependency on user that initialized it - auth()->id()
                'is_globally_accessible' => true,
                'description' => null,
            ]);
        }else{
            // Augment missing info
                $supplier->address = $supplier->address??  $result->formatted_address;
                $supplier->phone = $supplier->phone??  $result->international_phone_number;
                $supplier->website = $supplier->website??  $result->website;
                $supplier->update();
                return $supplier;
        }
    }

    /**
     * @param string $provider_name
     * @param string|null $provider_address
     * @param string|null $provider_phone
     * @param string|null $provider_website
     * @param string|null $provider_email
     * @param int $booking_category_id
     * @param int $created_by_id
     * @param bool $is_global
     * @return ServiceSupplier|Builder|\Illuminate\Database\Eloquent\Model|object
     */
    public static function createOrUpdateFromRequest(string $provider_name, ?string $provider_address,
                                                     ?string $provider_phone, ?string $provider_website,
                                                     ?string $provider_email, int $booking_category_id,
                                                     int $created_by_id, bool $is_global = false)
    {
        $supplier = self::getSupplier($provider_name, $booking_category_id );
        if( !$supplier )
        {
            return self::create([
                'name' => $provider_name,
                'address' => $provider_address,
                'phone' => $provider_phone,
                'website' => $provider_website,
                'email' => $provider_email,
                'booking_category_id' => $booking_category_id,
                'created_by_id' => $created_by_id,
                'is_globally_accessible' => $is_global,
                'description' => null,
            ]);
        }else{
            // Augment missing info
            $supplier->address = empty( $provider_address )? $supplier->address : $provider_address;
            $supplier->phone = empty( $provider_phone )? $supplier->phone : $provider_phone;
            $supplier->website = empty( $provider_website )? $supplier->website : $provider_website;
            $supplier->email = empty( $provider_email )? $supplier->email : $provider_email;
            $supplier->update();
            return $supplier;
        }
    }

    /**
     * @param string|null $description
     * @return $this
     */
    public function updateDescription(?string $description): ServiceSupplier
    {
        $this->description = empty( $description )? $this->description : $description ;
        $this->updateQuietly();
        return $this;
    }

    public function getFolderStorageRelativePath(): string
    {
        return sprintf("service-providers/%s", $this->id );
    }

    /**
     * Get full url path
     * @return string
     */
    public static function generateImageUrlFullPath(): string
    {
        return Storage::cloud()->url(  sprintf("service-providers/%s.png", Str::random( ) ) );
    }

//    /**
//     * this will generate a new url if current url is null or
//     * the current url is not hosted on this server.
//     * NB: Only call this if new image is uploaded
//     * @return string|null
//     */
//    public function forceGetImageUrlPathOnCloud()
//    {
//        if( ! $this->image_url || ! Str::contains( $this->image_url, env( 'AWS_URL' ) ) )
//            return self::generateImageUrlFullPath();
//
//        return $this->image_url;
//    }

    /**
     * @param string $name
     * @param int $booking_category_id
     * @return Builder|\Illuminate\Database\Eloquent\Model|object|null|ServiceSupplier
     */
    public static function getSupplier( string $name, int $booking_category_id )
    {
        return self::query()
            ->where("name", $name)
            ->where("booking_category_id", $booking_category_id)
            ->first();
    }

    /**
     * @inheritDoc
     */
    public function presentForDev(): array
    {
       return [
           'id' => $this->id,
           'name' => $this->name,
           'address' => $this->address,
           'phone' => $this->phone,
           'website' => $this->website,
           'email' => $this->email,
           'booking_category_id' => $this->booking_category_id,
           'created_by_id' => $this->created_by_id,
           'is_globally_accessible' => $this->is_globally_accessible,
           'description' => $this->description,
           'ref_id' => $this->ref_id,
//           'image_url' => $this->image_url,
           'created_at' => $this->created_at,

           "images" => $this->getPictures()->map->presentForDev()->toArray(),
//           "images" => $this->service_suppliers_pictures->map->only([ "id", "image_url" ] )->toArray(),

           // Generates error if relationship is not loaded
           // and if it can't force load the relationship in case the key is missing
           'booking_category' => optional($this->booking_category)->description,
           'creator' => $this->user->name,
       ];
    }

    /**
     * @inheritDoc
     */
    public function globalSearchResultView(): array
    {
        return array_merge(
            $this->only([
                'name',
                'address',
                'phone',
                'email',
                'id',
            ]),
            [
                'booking_category' => optional($this->booking_category)->description,
            ]
        );
    }

    public function getPictures(): Collection
    {
       return $this->service_suppliers_pictures;
    }

    public function addPicture(UploadedFile $image)
    {
        return $this->service_suppliers_pictures()->create([
            'image_relative_url' => ServiceSuppliersPicture::saveImageOnCloud( $image, $this )
        ]);
    }
}
