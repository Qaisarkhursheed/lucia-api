<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;

/**
 * Class CruiseCabin
 * 
 * @property int $id
 * @property int $itinerary_cruise_id
 * @property string $cabin_category
 * @property string|null $confirmation_reference
 * @property string $bedding_type
 * @property string|null $guest_name
 * @property int|null $number_of_guests
 * 
 * @property ItineraryCruise $itinerary_cruise
 *
 * @package App\Models
 */
class CruiseCabin extends ModelBase
{
	protected $table = 'cruise_cabin';
	public $timestamps = false;

	protected $casts = [
		'itinerary_cruise_id' => 'int',
		'number_of_guests' => 'int'
	];

	protected $fillable = [
		'itinerary_cruise_id',
		'cabin_category',
		'confirmation_reference',
		'bedding_type',
		'guest_name',
		'number_of_guests'
	];

	public function itinerary_cruise()
	{
		return $this->belongsTo(ItineraryCruise::class);
	}
}
