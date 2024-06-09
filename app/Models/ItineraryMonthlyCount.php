<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;

/**
 * Class ItineraryMonthlyCount
 * 
 * @property int|null $year_created
 * @property int|null $month_created
 * @property int $records
 *
 * @package App\Models
 */
class ItineraryMonthlyCount extends ModelBase
{
	protected $table = 'itinerary_monthly_count';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'year_created' => 'int',
		'month_created' => 'int',
		'records' => 'int'
	];

	protected $fillable = [
		'year_created',
		'month_created',
		'records'
	];
}
