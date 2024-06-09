<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;

/**
 * Class Amenity
 * 
 * @property int $id
 * @property string $description
 * @property string|null $image_relative_url
 * @property bool $important
 *
 * @package App\Models
 */
class Amenity extends ModelBase
{
	protected $table = 'amenities';
	public $timestamps = false;

	protected $casts = [
		'important' => 'bool'
	];

	protected $fillable = [
		'description',
		'image_relative_url',
		'important'
	];
}
