<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class ItineraryDivider
 * 
 * @property int $id
 * @property int $itinerary_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $target_date
 * @property int $booking_category_id
 * @property string|null $custom_header_title
 * @property int $sorting_rank
 * 
 * @property BookingCategory $booking_category
 * @property Itinerary $itinerary
 *
 * @package App\Models
 */
class ItineraryDivider extends ModelBase
{
	protected $table = 'itinerary_divider';

	protected $casts = [
		'itinerary_id' => 'int',
		'booking_category_id' => 'int',
		'sorting_rank' => 'int'
	];

	protected $dates = [
		'target_date'
	];

	protected $fillable = [
		'itinerary_id',
		'target_date',
		'booking_category_id',
		'custom_header_title',
		'sorting_rank'
	];

	public function booking_category()
	{
		return $this->belongsTo(BookingCategory::class);
	}

	public function itinerary()
	{
		return $this->belongsTo(Itinerary::class);
	}
}
