<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class HotelPicture
 * 
 * @property int $id
 * @property int $itinerary_hotel_id
 * @property string $image_url
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property ItineraryHotel $itinerary_hotel
 *
 * @package App\Models
 */
class HotelPicture extends ModelBase
{
	protected $table = 'hotel_pictures';

	protected $casts = [
		'itinerary_hotel_id' => 'int'
	];

	protected $fillable = [
		'itinerary_hotel_id',
		'image_url'
	];

	public function itinerary_hotel()
	{
		return $this->belongsTo(ItineraryHotel::class);
	}
}
