<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class ItineraryFlightSegment
 * 
 * @property int $id
 * @property string $flight_from
 * @property string $flight_to
 * @property string|null $flight_from_icao
 * @property string|null $flight_from_latitude
 * @property string|null $flight_from_longitude
 * @property string|null $flight_to_icao
 * @property string|null $flight_to_latitude
 * @property string|null $flight_to_longitude
 * @property string|null $airline
 * @property string|null $airline_operator
 * @property string $flight_number
 * @property int|null $duration_in_minutes
 * @property Carbon $departure_datetime
 * @property Carbon|null $arrival_datetime
 * @property int $itinerary_flight_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $flight_from_iata
 * @property string|null $flight_to_iata
 * 
 * @property ItineraryFlight $itinerary_flight
 *
 * @package App\Models
 */
class ItineraryFlightSegment extends ModelBase
{
	protected $table = 'itinerary_flight_segment';

	protected $casts = [
		'duration_in_minutes' => 'int',
		'itinerary_flight_id' => 'int'
	];

	protected $dates = [
		'departure_datetime',
		'arrival_datetime'
	];

	protected $fillable = [
		'flight_from',
		'flight_to',
		'flight_from_icao',
		'flight_from_latitude',
		'flight_from_longitude',
		'flight_to_icao',
		'flight_to_latitude',
		'flight_to_longitude',
		'airline',
		'airline_operator',
		'flight_number',
		'duration_in_minutes',
		'departure_datetime',
		'arrival_datetime',
		'itinerary_flight_id',
		'flight_from_iata',
		'flight_to_iata'
	];

	public function itinerary_flight()
	{
		return $this->belongsTo(ItineraryFlight::class);
	}
}
