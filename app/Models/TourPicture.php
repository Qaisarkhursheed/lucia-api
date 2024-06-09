<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class TourPicture
 * 
 * @property int $id
 * @property int $itinerary_tour_id
 * @property string $image_url
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property ItineraryTour $itinerary_tour
 *
 * @package App\Models
 */
class TourPicture extends ModelBase
{
	protected $table = 'tour_pictures';

	protected $casts = [
		'itinerary_tour_id' => 'int'
	];

	protected $fillable = [
		'itinerary_tour_id',
		'image_url'
	];

	public function itinerary_tour()
	{
		return $this->belongsTo(ItineraryTour::class);
	}
}
