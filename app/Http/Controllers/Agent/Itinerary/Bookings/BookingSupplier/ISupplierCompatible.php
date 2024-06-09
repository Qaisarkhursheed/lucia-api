<?php

namespace App\Http\Controllers\Agent\Itinerary\Bookings\BookingSupplier;

use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Reliese\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string|null $address
 * @property string|null $phone
 * @property string|null $website
 * @property string|null $email
 * @property int $booking_category_id
 * @property string|null $description
 */
interface ISupplierCompatible extends IDeveloperPresentationInterface
{
   public function getId():int;
   public function getName():string;
   public function getAddress():?string;
   public function getPhone():?string;
   public function getWebsite():?string;
   public function getEmail():?string;
   public function getDescription():?string;

   // implemented on model
   public function update(array $attributes = [], array $options = []);

    /**
     * @return Collection|ISupplierPictureCompatible[]
     */
   public function getPictures():Collection;

    /**
     * @param int $id
     * @return ISupplierPictureCompatible
     */
    public function getPicture(int $id): ISupplierPictureCompatible;

    /**
     * Removes Picture
     *
     * @param int $id
     * @return void
     */
   public function deletePicture(int $id):void;

    /**
     * Add supplier picture
     *
     * @param UploadedFile $image
     * @return ISupplierPictureCompatible|Model
     */
   public function addPicture(UploadedFile $image);

}
