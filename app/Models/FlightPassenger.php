<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class FlightPassenger
 * 
 * @property int $id
 * @property int $itinerary_flight_id
 * @property int $itinerary_passenger_id
 * @property string|null $seat
 * @property string|null $class
 * @property string|null $frequent_flyer_number
 * @property string|null $ticket_number
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property ItineraryFlight $itinerary_flight
 * @property ItineraryPassenger $itinerary_passenger
 *
 * @package App\Models
 */
class FlightPassenger extends ModelBase
{
	protected $table = 'flight_passengers';

	protected $casts = [
		'itinerary_flight_id' => 'int',
		'itinerary_passenger_id' => 'int'
	];

	protected $fillable = [
		'itinerary_flight_id',
		'itinerary_passenger_id',
		'seat',
		'class',
		'frequent_flyer_number',
		'ticket_number'
	];

	public function itinerary_flight()
	{
		return $this->belongsTo(ItineraryFlight::class);
	}

	public function itinerary_passenger()
	{
		return $this->belongsTo(ItineraryPassenger::class);
	}
}
