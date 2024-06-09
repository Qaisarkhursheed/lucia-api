<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;

/**
 * Class PreferredTimezone
 * 
 * @property int $id
 * @property string $timezone_id
 * @property string|null $country_name
 * @property int|null $offset_seconds
 * @property int|null $offset_minutes
 * @property string|null $offset_gmt
 * @property string|null $offset_tzid
 * @property string|null $offset_tzab
 * @property string|null $offset_tzfull
 *
 * @package App\Models
 */
class PreferredTimezone extends ModelBase
{
	protected $table = 'preferred_timezones';
	public $timestamps = false;

	protected $casts = [
		'offset_seconds' => 'int',
		'offset_minutes' => 'int'
	];

	protected $fillable = [
		'timezone_id',
		'country_name',
		'offset_seconds',
		'offset_minutes',
		'offset_gmt',
		'offset_tzid',
		'offset_tzab',
		'offset_tzfull'
	];
}
