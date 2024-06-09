<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class ConciergeSupplier
 * 
 * @property int $id
 * @property int $itinerary_concierge_id
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
 * @property ItineraryConcierge $itinerary_concierge
 *
 * @package App\Models
 */
class ConciergeSupplier extends ModelBase
{
	protected $table = 'concierge_suppliers';

	protected $casts = [
		'itinerary_concierge_id' => 'int',
		'save_to_library' => 'bool'
	];

	protected $fillable = [
		'itinerary_concierge_id',
		'name',
		'address',
		'phone',
		'website',
		'email',
		'save_to_library',
		'description'
	];

	public function itinerary_concierge()
	{
		return $this->belongsTo(ItineraryConcierge::class);
	}
}
