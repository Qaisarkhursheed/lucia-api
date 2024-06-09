<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class HotelAmenity
 * 
 * @property int $id
 * @property int $itinerary_hotel_id
 * @property string $amenity
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property ItineraryHotel $itinerary_hotel
 *
 * @package App\Models
 */
class HotelAmenity extends ModelBase
{
	protected $table = 'hotel_amenities';

	protected $casts = [
		'itinerary_hotel_id' => 'int'
	];

	protected $fillable = [
		'itinerary_hotel_id',
		'amenity'
	];

	public function itinerary_hotel()
	{
		return $this->belongsTo(ItineraryHotel::class);
	}
}
