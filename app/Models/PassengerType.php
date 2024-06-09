<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class PassengerType
 * 
 * @property int $id
 * @property string $description
 * 
 * @property Collection|ItineraryPassenger[] $itinerary_passengers
 *
 * @package App\Models
 */
class PassengerType extends ModelBase
{
	protected $table = 'passenger_type';
	public $timestamps = false;

	protected $fillable = [
		'description'
	];

	public function itinerary_passengers()
	{
		return $this->hasMany(ItineraryPassenger::class);
	}
}
