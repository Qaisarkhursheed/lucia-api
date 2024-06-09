<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ItineraryHotel
 * 
 * @property int $id
 * @property float|null $price
 * @property Carbon $check_in_date
 * @property Carbon $check_out_date
 * @property int|null $travelers
 * @property string|null $confirmation_reference
 * @property int $itinerary_id
 * @property string|null $cancel_policy
 * @property string|null $notes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $payment
 * @property string|null $check_in_time
 * @property string|null $check_out_time
 * @property int $booking_category_id
 * @property string|null $custom_header_title
 * @property string|null $google_calendar_event_id
 * @property int $sorting_rank
 * 
 * @property BookingCategory $booking_category
 * @property Itinerary $itinerary
 * @property Collection|HotelAmenity[] $hotel_amenities
 * @property Collection|HotelPassenger[] $hotel_passengers
 * @property Collection|HotelPicture[] $hotel_pictures
 * @property Collection|HotelRoom[] $hotel_rooms
 * @property HotelSupplier $hotel_supplier
 *
 * @package App\Models
 */
class ItineraryHotel extends ModelBase
{
	protected $table = 'itinerary_hotels';

	protected $casts = [
		'price' => 'float',
		'travelers' => 'int',
		'itinerary_id' => 'int',
		'booking_category_id' => 'int',
		'sorting_rank' => 'int'
	];

	protected $dates = [
		'check_in_date',
		'check_out_date'
	];

	protected $fillable = [
		'price',
		'check_in_date',
		'check_out_date',
		'travelers',
		'confirmation_reference',
		'itinerary_id',
		'cancel_policy',
		'notes',
		'payment',
		'check_in_time',
		'check_out_time',
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

	public function hotel_amenities()
	{
		return $this->hasMany(HotelAmenity::class);
	}

	public function hotel_passengers()
	{
		return $this->hasMany(HotelPassenger::class);
	}

	public function hotel_pictures()
	{
		return $this->hasMany(HotelPicture::class);
	}

	public function hotel_rooms()
	{
		return $this->hasMany(HotelRoom::class);
	}

	public function hotel_supplier()
	{
		return $this->hasOne(HotelSupplier::class);
	}
}
