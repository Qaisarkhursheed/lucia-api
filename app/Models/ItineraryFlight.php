<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ItineraryFlight
 * 
 * @property int $id
 * @property int $itinerary_id
 * @property string|null $cancel_policy
 * @property string|null $notes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $booking_category_id
 * @property string|null $confirmation_number
 * @property string|null $custom_header_title
 * @property string|null $google_calendar_event_id
 * @property string|null $price
 * @property string|null $check_in_url
 * @property int $sorting_rank
 * 
 * @property BookingCategory $booking_category
 * @property Itinerary $itinerary
 * @property Collection|FlightPassenger[] $flight_passengers
 * @property Collection|FlightPicture[] $flight_pictures
 * @property FlightSupplier $flight_supplier
 * @property Collection|ItineraryFlightSegment[] $itinerary_flight_segments
 *
 * @package App\Models
 */
class ItineraryFlight extends ModelBase
{
	protected $table = 'itinerary_flights';

	protected $casts = [
		'itinerary_id' => 'int',
		'booking_category_id' => 'int',
		'sorting_rank' => 'int'
	];

	protected $fillable = [
		'itinerary_id',
		'cancel_policy',
		'notes',
		'booking_category_id',
		'confirmation_number',
		'custom_header_title',
		'google_calendar_event_id',
		'price',
		'check_in_url',
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

	public function flight_passengers()
	{
		return $this->hasMany(FlightPassenger::class);
	}

	public function flight_pictures()
	{
		return $this->hasMany(FlightPicture::class);
	}

	public function flight_supplier()
	{
		return $this->hasOne(FlightSupplier::class);
	}

	public function itinerary_flight_segments()
	{
		return $this->hasMany(ItineraryFlightSegment::class);
	}
}
