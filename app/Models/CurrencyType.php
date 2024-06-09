<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class CurrencyType
 * 
 * @property int $id
 * @property string $description
 * 
 * @property Collection|HotelRoom[] $hotel_rooms
 * @property Collection|Itinerary[] $itineraries
 * @property Collection|User[] $users
 *
 * @package App\Models
 */
class CurrencyType extends ModelBase
{
	protected $table = 'currency_types';
	public $timestamps = false;

	protected $fillable = [
		'description'
	];

	public function hotel_rooms()
	{
		return $this->hasMany(HotelRoom::class, 'currency_id');
	}

	public function itineraries()
	{
		return $this->hasMany(Itinerary::class, 'currency_id');
	}

	public function users()
	{
		return $this->hasMany(User::class, 'default_currency_id');
	}
}
