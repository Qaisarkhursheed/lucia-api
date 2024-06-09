<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class ItineraryTask
 * 
 * @property int $id
 * @property int|null $itinerary_id
 * @property string $title
 * @property Carbon|null $deadline
 * @property string|null $notes
 * @property bool $is_completed
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property Itinerary|null $itinerary
 *
 * @package App\Models
 */
class ItineraryTask extends ModelBase
{
	protected $table = 'itinerary_tasks';

	protected $casts = [
		'itinerary_id' => 'int',
		'is_completed' => 'bool'
	];

	protected $dates = [
		'deadline'
	];

	protected $fillable = [
		'itinerary_id',
		'title',
		'deadline',
		'notes',
		'is_completed'
	];

	public function itinerary()
	{
		return $this->belongsTo(Itinerary::class);
	}
}
