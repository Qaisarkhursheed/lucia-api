<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class ItineraryDocument
 * 
 * @property int $id
 * @property string $document_relative_url
 * @property int $itinerary_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $name
 * 
 * @property Itinerary $itinerary
 *
 * @package App\Models
 */
class ItineraryDocument extends ModelBase
{
	protected $table = 'itinerary_documents';

	protected $casts = [
		'itinerary_id' => 'int'
	];

	protected $fillable = [
		'document_relative_url',
		'itinerary_id',
		'name'
	];

	public function itinerary()
	{
		return $this->belongsTo(Itinerary::class);
	}
}
