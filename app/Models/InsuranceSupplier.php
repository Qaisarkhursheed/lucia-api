<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class InsuranceSupplier
 * 
 * @property int $id
 * @property int $itinerary_insurance_id
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
 * @property ItineraryInsurance $itinerary_insurance
 *
 * @package App\Models
 */
class InsuranceSupplier extends ModelBase
{
	protected $table = 'insurance_suppliers';

	protected $casts = [
		'itinerary_insurance_id' => 'int',
		'save_to_library' => 'bool'
	];

	protected $fillable = [
		'itinerary_insurance_id',
		'name',
		'address',
		'phone',
		'website',
		'email',
		'save_to_library',
		'description'
	];

	public function itinerary_insurance()
	{
		return $this->belongsTo(ItineraryInsurance::class);
	}
}
