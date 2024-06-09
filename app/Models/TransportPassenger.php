<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class TransportPassenger
 * 
 * @property int $id
 * @property int $itinerary_transport_id
 * @property int $itinerary_passenger_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $seat
 * @property string|null $class
 * @property string|null $frequent_flyer_number
 * @property string|null $ticket_number
 * 
 * @property ItineraryPassenger $itinerary_passenger
 * @property ItineraryTransport $itinerary_transport
 *
 * @package App\Models
 */
class TransportPassenger extends ModelBase
{
	protected $table = 'transport_passengers';

	protected $casts = [
		'itinerary_transport_id' => 'int',
		'itinerary_passenger_id' => 'int'
	];

	protected $fillable = [
		'itinerary_transport_id',
		'itinerary_passenger_id',
		'seat',
		'class',
		'frequent_flyer_number',
		'ticket_number'
	];

	public function itinerary_passenger()
	{
		return $this->belongsTo(ItineraryPassenger::class);
	}

	public function itinerary_transport()
	{
		return $this->belongsTo(ItineraryTransport::class);
	}
}
