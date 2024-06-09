<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;

/**
 * Class Airport
 * 
 * @property int $id
 * @property string|null $fs
 * @property string $iata
 * @property string|null $icao
 * @property string|null $faa
 * @property string $name
 * @property string|null $city
 * @property string|null $countryCode
 * @property string|null $countryName
 * @property string|null $regionName
 * @property string|null $timeZoneRegionName
 * @property string|null $weatherZone
 * @property int|null $utcOffsetHours
 * @property string|null $latitude
 * @property string|null $longitude
 * @property string|null $elevationFeet
 * @property int|null $classification
 * @property bool|null $active
 * @property string|null $weatherUrl
 * @property string|null $delayIndexUrl
 *
 * @package App\Models
 */
class Airport extends ModelBase
{
	protected $table = 'airports';
	public $timestamps = false;

	protected $casts = [
		'utcOffsetHours' => 'int',
		'classification' => 'int',
		'active' => 'bool'
	];

	protected $fillable = [
		'fs',
		'iata',
		'icao',
		'faa',
		'name',
		'city',
		'countryCode',
		'countryName',
		'regionName',
		'timeZoneRegionName',
		'weatherZone',
		'utcOffsetHours',
		'latitude',
		'longitude',
		'elevationFeet',
		'classification',
		'active',
		'weatherUrl',
		'delayIndexUrl'
	];
}
