<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class HotelRoom
 * 
 * @property int $id
 * @property int $itinerary_hotel_id
 * @property string $room_type
 * @property string|null $guest_name
 * @property float|null $room_rate
 * @property int $currency_id
 * @property string|null $room_description
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int|null $number_of_guests
 * @property string|null $relative_image_url
 * @property string $bedding_type
 * 
 * @property CurrencyType $currency_type
 * @property ItineraryHotel $itinerary_hotel
 *
 * @package App\Models
 */
class HotelRoom extends ModelBase
{
	protected $table = 'hotel_room';

	protected $casts = [
		'itinerary_hotel_id' => 'int',
		'room_rate' => 'float',
		'currency_id' => 'int',
		'number_of_guests' => 'int'
	];

	protected $fillable = [
		'itinerary_hotel_id',
		'room_type',
		'guest_name',
		'room_rate',
		'currency_id',
		'room_description',
		'number_of_guests',
		'relative_image_url',
		'bedding_type'
	];

	public function currency_type()
	{
		return $this->belongsTo(CurrencyType::class, 'currency_id');
	}

	public function itinerary_hotel()
	{
		return $this->belongsTo(ItineraryHotel::class);
	}
}
