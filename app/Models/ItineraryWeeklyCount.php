<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class ItineraryWeeklyCount
 * 
 * @property int|null $year_created
 * @property int|null $week_created
 * @property Carbon|null $any_date
 * @property int $records
 *
 * @package App\Models
 */
class ItineraryWeeklyCount extends ModelBase
{
	protected $table = 'itinerary_weekly_count';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'year_created' => 'int',
		'week_created' => 'int',
		'records' => 'int'
	];

	protected $dates = [
		'any_date'
	];

	protected $fillable = [
		'year_created',
		'week_created',
		'any_date',
		'records'
	];
}
