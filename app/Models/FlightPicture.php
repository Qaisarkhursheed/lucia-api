<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class FlightPicture
 * 
 * @property int $id
 * @property int $itinerary_flight_id
 * @property string $image_url
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property ItineraryFlight $itinerary_flight
 *
 * @package App\Models
 */
class FlightPicture extends ModelBase
{
	protected $table = 'flight_pictures';

	protected $casts = [
		'itinerary_flight_id' => 'int'
	];

	protected $fillable = [
		'itinerary_flight_id',
		'image_url'
	];

	public function itinerary_flight()
	{
		return $this->belongsTo(ItineraryFlight::class);
	}
}
