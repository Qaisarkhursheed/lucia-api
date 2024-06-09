<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;

/**
 * Class Airline
 * 
 * @property int $id
 * @property string $iata
 * @property string $icao
 * @property string $name
 *
 * @package App\Models
 */
class Airline extends ModelBase
{
	protected $table = 'airlines';
	public $timestamps = false;

	protected $fillable = [
		'iata',
		'icao',
		'name'
	];
}
