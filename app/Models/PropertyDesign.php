<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class PropertyDesign
 * 
 * @property int $id
 * @property string $description
 * 
 * @property Collection|DefaultItineraryTheme[] $default_itinerary_themes
 * @property Collection|ItineraryTheme[] $itinerary_themes
 *
 * @package App\Models
 */
class PropertyDesign extends ModelBase
{
	protected $table = 'property_design';
	public $timestamps = false;

	protected $fillable = [
		'description'
	];

	public function default_itinerary_themes()
	{
		return $this->hasMany(DefaultItineraryTheme::class);
	}

	public function itinerary_themes()
	{
		return $this->hasMany(ItineraryTheme::class);
	}
}
