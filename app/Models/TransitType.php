<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class TransitType
 * 
 * @property int $id
 * @property string $description
 * 
 * @property Collection|ItineraryTransport[] $itinerary_transports
 *
 * @package App\Models
 */
class TransitType extends ModelBase
{
	protected $table = 'transit_type';
	public $timestamps = false;

	protected $fillable = [
		'description'
	];

	public function itinerary_transports()
	{
		return $this->hasMany(ItineraryTransport::class);
	}
}
