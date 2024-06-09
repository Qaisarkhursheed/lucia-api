<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class TourSupplier
 * 
 * @property int $id
 * @property int $itinerary_tour_id
 * @property string $name
 * @property string|null $address
 * @property string|null $phone
 * @property string|null $website
 * @property string|null $email
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property bool $save_to_library
 * @property string|null $description
 * 
 * @property ItineraryTour $itinerary_tour
 *
 * @package App\Models
 */
class TourSupplier extends ModelBase
{
	protected $table = 'tour_suppliers';

	protected $casts = [
		'itinerary_tour_id' => 'int',
		'save_to_library' => 'bool'
	];

	protected $fillable = [
		'itinerary_tour_id',
		'name',
		'address',
		'phone',
		'website',
		'email',
		'save_to_library',
		'description'
	];

	public function itinerary_tour()
	{
		return $this->belongsTo(ItineraryTour::class);
	}
}
