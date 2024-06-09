<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier;

use App\ModelsExtended\BookingCategory;
use App\ModelsExtended\LinkedSupplierPresentationTrait;
use App\ModelsExtended\ServiceSupplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $name
 * @property string|null $address
 * @property string|null $phone
 * @property string|null $website
 * @property string|null $email
 * @property int $booking_category_id
 * @property string|null $description
 * @extends Model
 */
trait SupplierCompatibleTrait
{
    use LinkedSupplierPresentationTrait;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAddress(): ?string
    {
        return $this->address;

    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getPictures(): Collection
    {
       return collect();
    }

    public function getPicture(int $id): ISupplierPictureCompatible
    {
        return $this->getPictures()->where("id", $id)->firstOrFail();
    }

    public function deletePicture(int $id): void
    {
        $pic = $this->getPicture($id);
        Storage::cloud()->delete( $pic->getRelativeImageUrl() );
        $pic->delete();
    }

    /**
     * @return HasOne|null|ServiceSupplier
     */
    public function getLinkedServiceSupplier(int $booking_category_id): ?HasOne
    {
        return $this->hasOne(ServiceSupplier::class , 'name', 'name' )
            ->where("service_suppliers.booking_category_id", $booking_category_id );
    }
}
