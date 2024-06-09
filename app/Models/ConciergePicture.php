<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class ConciergePicture
 * 
 * @property int $id
 * @property int $itinerary_concierge_id
 * @property string $image_url
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property ItineraryConcierge $itinerary_concierge
 *
 * @package App\Models
 */
class ConciergePicture extends ModelBase
{
	protected $table = 'concierge_pictures';

	protected $casts = [
		'itinerary_concierge_id' => 'int'
	];

	protected $fillable = [
		'itinerary_concierge_id',
		'image_url'
	];

	public function itinerary_concierge()
	{
		return $this->belongsTo(ItineraryConcierge::class);
	}
}
