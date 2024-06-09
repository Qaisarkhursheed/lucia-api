<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class CruisePicture
 * 
 * @property int $id
 * @property int $itinerary_cruise_id
 * @property string $image_url
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property ItineraryCruise $itinerary_cruise
 *
 * @package App\Models
 */
class CruisePicture extends ModelBase
{
	protected $table = 'cruise_pictures';

	protected $casts = [
		'itinerary_cruise_id' => 'int'
	];

	protected $fillable = [
		'itinerary_cruise_id',
		'image_url'
	];

	public function itinerary_cruise()
	{
		return $this->belongsTo(ItineraryCruise::class);
	}
}
