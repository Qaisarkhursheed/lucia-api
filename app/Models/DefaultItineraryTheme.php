<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class DefaultItineraryTheme
 * 
 * @property int $id
 * @property int $user_id
 * @property int|null $property_design_id
 * @property string|null $itinerary_logo_relative_url
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property PropertyDesign|null $property_design
 * @property User $user
 *
 * @package App\Models
 */
class DefaultItineraryTheme extends ModelBase
{
	protected $table = 'default_itinerary_theme';

	protected $casts = [
		'user_id' => 'int',
		'property_design_id' => 'int'
	];

	protected $fillable = [
		'user_id',
		'property_design_id',
		'itinerary_logo_relative_url'
	];

	public function property_design()
	{
		return $this->belongsTo(PropertyDesign::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
