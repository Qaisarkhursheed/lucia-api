<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ItineraryCruise
 * 
 * @property int $id
 * @property string $departure_port_city
 * @property string $arrival_port_city
 * @property Carbon $departure_datetime
 * @property Carbon $disembarkation_datetime
 * @property int $itinerary_id
 * @property string|null $cancel_policy
 * @property string|null $notes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $cruise_ship_name
 * @property int $booking_category_id
 * @property float|null $departure_longitude
 * @property float|null $departure_latitude
 * @property float|null $arrival_longitude
 * @property float|null $arrival_latitude
 * @property string|null $custom_header_title
 * @property string|null $google_calendar_event_id
 * @property int $sorting_rank
 * 
 * @property BookingCategory $booking_category
 * @property Itinerary $itinerary
 * @property Collection|CruiseCabin[] $cruise_cabins
 * @property Collection|CruisePassenger[] $cruise_passengers
 * @property Collection|CruisePicture[] $cruise_pictures
 * @property CruiseSupplier $cruise_supplier
 *
 * @package App\Models
 */
class ItineraryCruise extends ModelBase
{
	protected $table = 'itinerary_cruises';

	protected $casts = [
		'itinerary_id' => 'int',
		'booking_category_id' => 'int',
		'departure_longitude' => 'float',
		'departure_latitude' => 'float',
		'arrival_longitude' => 'float',
		'arrival_latitude' => 'float',
		'sorting_rank' => 'int'
	];

	protected $dates = [
		'departure_datetime',
		'disembarkation_datetime'
	];

	protected $fillable = [
		'departure_port_city',
		'arrival_port_city',
		'departure_datetime',
		'disembarkation_datetime',
		'itinerary_id',
		'cancel_policy',
		'notes',
		'cruise_ship_name',
		'booking_category_id',
		'departure_longitude',
		'departure_latitude',
		'arrival_longitude',
		'arrival_latitude',
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

	public function cruise_cabins()
	{
		return $this->hasMany(CruiseCabin::class);
	}

	public function cruise_passengers()
	{
		return $this->hasMany(CruisePassenger::class);
	}

	public function cruise_pictures()
	{
		return $this->hasMany(CruisePicture::class);
	}

	public function cruise_supplier()
	{
		return $this->hasOne(CruiseSupplier::class);
	}
}
