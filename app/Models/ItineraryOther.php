<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class ItineraryOther
 * 
 * @property int $id
 * @property int $itinerary_id
 * @property string $title
 * @property string|null $notes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property bool|null $show_on_top
 * @property Carbon $target_date
 * @property int $booking_category_id
 * @property string|null $custom_header_title
 * @property string|null $google_calendar_event_id
 * @property bool $save_to_library
 * @property int $sorting_rank
 * 
 * @property BookingCategory $booking_category
 * @property Itinerary $itinerary
 *
 * @package App\Models
 */
class ItineraryOther extends ModelBase
{
	protected $table = 'itinerary_others';

	protected $casts = [
		'itinerary_id' => 'int',
		'show_on_top' => 'bool',
		'booking_category_id' => 'int',
		'save_to_library' => 'bool',
		'sorting_rank' => 'int'
	];

	protected $dates = [
		'target_date'
	];

	protected $fillable = [
		'itinerary_id',
		'title',
		'notes',
		'show_on_top',
		'target_date',
		'booking_category_id',
		'custom_header_title',
		'google_calendar_event_id',
		'save_to_library',
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
