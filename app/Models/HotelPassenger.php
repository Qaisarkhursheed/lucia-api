<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class HotelPassenger
 * 
 * @property int $id
 * @property int $itinerary_hotel_id
 * @property int $itinerary_passenger_id
 * @property string|null $room
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property ItineraryHotel $itinerary_hotel
 * @property ItineraryPassenger $itinerary_passenger
 *
 * @package App\Models
 */
class HotelPassenger extends ModelBase
{
	protected $table = 'hotel_passengers';

	protected $casts = [
		'itinerary_hotel_id' => 'int',
		'itinerary_passenger_id' => 'int'
	];

	protected $fillable = [
		'itinerary_hotel_id',
		'itinerary_passenger_id',
		'room'
	];

	public function itinerary_hotel()
	{
		return $this->belongsTo(ItineraryHotel::class);
	}

	public function itinerary_passenger()
	{
		return $this->belongsTo(ItineraryPassenger::class);
	}
}
