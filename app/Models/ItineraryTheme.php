<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class ItineraryTheme
 * 
 * @property int $id
 * @property int $itinerary_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int|null $abstract_position_id
 * @property bool|null $hide_abstract
 * @property int|null $property_design_id
 * @property string|null $itinerary_logo_url
 * 
 * @property PropertyPosition|null $property_position
 * @property Itinerary $itinerary
 * @property PropertyDesign|null $property_design
 *
 * @package App\Models
 */
class ItineraryTheme extends ModelBase
{
	protected $table = 'itinerary_theme';

	protected $casts = [
		'itinerary_id' => 'int',
		'abstract_position_id' => 'int',
		'hide_abstract' => 'bool',
		'property_design_id' => 'int'
	];

	protected $fillable = [
		'itinerary_id',
		'abstract_position_id',
		'hide_abstract',
		'property_design_id',
		'itinerary_logo_url'
	];

	public function property_position()
	{
		return $this->belongsTo(PropertyPosition::class, 'abstract_position_id');
	}

	public function itinerary()
	{
		return $this->belongsTo(Itinerary::class);
	}

	public function property_design()
	{
		return $this->belongsTo(PropertyDesign::class);
	}
}
