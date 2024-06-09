<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class ItineraryPicture
 * 
 * @property int $id
 * @property string $image_url
 * @property int $itinerary_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property Itinerary $itinerary
 *
 * @package App\Models
 */
class ItineraryPicture extends ModelBase
{
	protected $table = 'itinerary_pictures';

	protected $casts = [
		'itinerary_id' => 'int'
	];

	protected $fillable = [
		'image_url',
		'itinerary_id'
	];

	public function itinerary()
	{
		return $this->belongsTo(Itinerary::class);
	}
}
