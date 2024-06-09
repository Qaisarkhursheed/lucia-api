<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;

/**
 * Class DbTimezone
 * 
 * @property int $id
 * @property int|null $offset_seconds
 * @property int|null $offset_minutes
 * @property string|null $offset_gmt
 * @property string|null $offset_tzid
 * @property string|null $offset_tzab
 * @property string|null $offset_tzfull
 * @property string|null $country_name
 * @property string $timezone_id
 *
 * @package App\Models
 */
class DbTimezone extends ModelBase
{
	protected $table = 'db_timezone';
	public $timestamps = false;

	protected $casts = [
		'offset_seconds' => 'int',
		'offset_minutes' => 'int'
	];

	protected $fillable = [
		'offset_seconds',
		'offset_minutes',
		'offset_gmt',
		'offset_tzid',
		'offset_tzab',
		'offset_tzfull',
		'country_name',
		'timezone_id'
	];
}
