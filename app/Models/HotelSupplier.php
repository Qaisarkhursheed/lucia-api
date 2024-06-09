<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class HotelSupplier
 * 
 * @property int $id
 * @property int $itinerary_hotel_id
 * @property string $name
 * @property string|null $address
 * @property string|null $phone
 * @property string|null $website
 * @property string|null $email
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property bool $save_to_library
 * @property string|null $description
 * 
 * @property ItineraryHotel $itinerary_hotel
 *
 * @package App\Models
 */
class HotelSupplier extends ModelBase
{
	protected $table = 'hotel_suppliers';

	protected $casts = [
		'itinerary_hotel_id' => 'int',
		'save_to_library' => 'bool'
	];

	protected $fillable = [
		'itinerary_hotel_id',
		'name',
		'address',
		'phone',
		'website',
		'email',
		'save_to_library',
		'description'
	];

	public function itinerary_hotel()
	{
		return $this->belongsTo(ItineraryHotel::class);
	}
}
