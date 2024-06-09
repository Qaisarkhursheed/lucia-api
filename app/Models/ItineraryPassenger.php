<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ItineraryPassenger
 * 
 * @property int $id
 * @property string $name
 * @property int $itinerary_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $passenger_type_id
 * 
 * @property Itinerary $itinerary
 * @property PassengerType $passenger_type
 * @property Collection|CruisePassenger[] $cruise_passengers
 * @property Collection|FlightPassenger[] $flight_passengers
 * @property Collection|HotelPassenger[] $hotel_passengers
 * @property Collection|TransportPassenger[] $transport_passengers
 *
 * @package App\Models
 */
class ItineraryPassenger extends ModelBase
{
	protected $table = 'itinerary_passenger';

	protected $casts = [
		'itinerary_id' => 'int',
		'passenger_type_id' => 'int'
	];

	protected $fillable = [
		'name',
		'itinerary_id',
		'passenger_type_id'
	];

	public function itinerary()
	{
		return $this->belongsTo(Itinerary::class);
	}

	public function passenger_type()
	{
		return $this->belongsTo(PassengerType::class);
	}

	public function cruise_passengers()
	{
		return $this->hasMany(CruisePassenger::class);
	}

	public function flight_passengers()
	{
		return $this->hasMany(FlightPassenger::class);
	}

	public function hotel_passengers()
	{
		return $this->hasMany(HotelPassenger::class);
	}

	public function transport_passengers()
	{
		return $this->hasMany(TransportPassenger::class);
	}
}
