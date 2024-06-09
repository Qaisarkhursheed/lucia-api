<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class CruiseSupplier
 * 
 * @property int $id
 * @property int $itinerary_cruise_id
 * @property string $name
 * @property string|null $address
 * @property string|null $phone
 * @property string|null $website
 * @property string|null $email
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $description
 * @property bool $save_to_library
 * 
 * @property ItineraryCruise $itinerary_cruise
 *
 * @package App\Models
 */
class CruiseSupplier extends ModelBase
{
	protected $table = 'cruise_suppliers';

	protected $casts = [
		'itinerary_cruise_id' => 'int',
		'save_to_library' => 'bool'
	];

	protected $fillable = [
		'itinerary_cruise_id',
		'name',
		'address',
		'phone',
		'website',
		'email',
		'description',
		'save_to_library'
	];

	public function itinerary_cruise()
	{
		return $this->belongsTo(ItineraryCruise::class);
	}
}
