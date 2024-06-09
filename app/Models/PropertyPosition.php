<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class PropertyPosition
 * 
 * @property int $id
 * @property string $description
 * 
 * @property Collection|ItineraryTheme[] $itinerary_themes
 *
 * @package App\Models
 */
class PropertyPosition extends ModelBase
{
	protected $table = 'property_position';
	public $timestamps = false;

	protected $fillable = [
		'description'
	];

	public function itinerary_themes()
	{
		return $this->hasMany(ItineraryTheme::class, 'abstract_position_id');
	}
}
