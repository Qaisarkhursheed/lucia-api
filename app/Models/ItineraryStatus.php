<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ItineraryStatus
 * 
 * @property int $id
 * @property string $description
 * 
 * @property Collection|Itinerary[] $itineraries
 *
 * @package App\Models
 */
class ItineraryStatus extends ModelBase
{
	protected $table = 'itinerary_status';
	public $timestamps = false;

	protected $fillable = [
		'description'
	];

	public function itineraries()
	{
		return $this->hasMany(Itinerary::class, 'status_id');
	}
}
