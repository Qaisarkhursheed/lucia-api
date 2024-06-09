<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class InsurancePicture
 * 
 * @property int $id
 * @property int $itinerary_insurance_id
 * @property string $image_url
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property ItineraryInsurance $itinerary_insurance
 *
 * @package App\Models
 */
class InsurancePicture extends ModelBase
{
	protected $table = 'insurance_pictures';

	protected $casts = [
		'itinerary_insurance_id' => 'int'
	];

	protected $fillable = [
		'itinerary_insurance_id',
		'image_url'
	];

	public function itinerary_insurance()
	{
		return $this->belongsTo(ItineraryInsurance::class);
	}
}
