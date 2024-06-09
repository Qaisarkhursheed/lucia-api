<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class FlightSupplier
 * 
 * @property int $id
 * @property int $itinerary_flight_id
 * @property string $name
 * @property string|null $address
 * @property string|null $phone
 * @property string|null $website
 * @property string|null $email
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property ItineraryFlight $itinerary_flight
 *
 * @package App\Models
 */
class FlightSupplier extends ModelBase
{
	protected $table = 'flight_suppliers';

	protected $casts = [
		'itinerary_flight_id' => 'int'
	];

	protected $fillable = [
		'itinerary_flight_id',
		'name',
		'address',
		'phone',
		'website',
		'email'
	];

	public function itinerary_flight()
	{
		return $this->belongsTo(ItineraryFlight::class);
	}
}
