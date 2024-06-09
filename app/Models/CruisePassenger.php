<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class CruisePassenger
 * 
 * @property int $id
 * @property int $itinerary_cruise_id
 * @property int $itinerary_passenger_id
 * @property string|null $cabin
 * @property string|null $cabin_category
 * @property string $ticket_number
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property ItineraryCruise $itinerary_cruise
 * @property ItineraryPassenger $itinerary_passenger
 *
 * @package App\Models
 */
class CruisePassenger extends ModelBase
{
	protected $table = 'cruise_passengers';

	protected $casts = [
		'itinerary_cruise_id' => 'int',
		'itinerary_passenger_id' => 'int'
	];

	protected $fillable = [
		'itinerary_cruise_id',
		'itinerary_passenger_id',
		'cabin',
		'cabin_category',
		'ticket_number'
	];

	public function itinerary_cruise()
	{
		return $this->belongsTo(ItineraryCruise::class);
	}

	public function itinerary_passenger()
	{
		return $this->belongsTo(ItineraryPassenger::class);
	}
}
