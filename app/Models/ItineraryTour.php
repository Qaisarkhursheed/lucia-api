<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ItineraryTour
 * 
 * @property int $id
 * @property int $itinerary_id
 * @property float|null $price
 * @property string|null $payment
 * @property string|null $confirmation_reference
 * @property Carbon|null $start_datetime
 * @property Carbon|null $end_datetime
 * @property string|null $meeting_point
 * @property string|null $confirmed_by
 * @property string|null $description
 * @property string|null $notes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $booking_category_id
 * @property string|null $custom_header_title
 * @property string|null $google_calendar_event_id
 * @property int $sorting_rank
 * 
 * @property BookingCategory $booking_category
 * @property Itinerary $itinerary
 * @property Collection|TourPicture[] $tour_pictures
 * @property TourSupplier $tour_supplier
 *
 * @package App\Models
 */
class ItineraryTour extends ModelBase
{
	protected $table = 'itinerary_tours';

	protected $casts = [
		'itinerary_id' => 'int',
		'price' => 'float',
		'booking_category_id' => 'int',
		'sorting_rank' => 'int'
	];

	protected $dates = [
		'start_datetime',
		'end_datetime'
	];

	protected $fillable = [
		'itinerary_id',
		'price',
		'payment',
		'confirmation_reference',
		'start_datetime',
		'end_datetime',
		'meeting_point',
		'confirmed_by',
		'description',
		'notes',
		'booking_category_id',
		'custom_header_title',
		'google_calendar_event_id',
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

	public function tour_pictures()
	{
		return $this->hasMany(TourPicture::class);
	}

	public function tour_supplier()
	{
		return $this->hasOne(TourSupplier::class);
	}
}
