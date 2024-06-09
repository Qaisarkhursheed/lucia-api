<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class TransportPicture
 * 
 * @property int $id
 * @property int $itinerary_transport_id
 * @property string $image_url
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property ItineraryTransport $itinerary_transport
 *
 * @package App\Models
 */
class TransportPicture extends ModelBase
{
	protected $table = 'transport_pictures';

	protected $casts = [
		'itinerary_transport_id' => 'int'
	];

	protected $fillable = [
		'itinerary_transport_id',
		'image_url'
	];

	public function itinerary_transport()
	{
		return $this->belongsTo(ItineraryTransport::class);
	}
}
