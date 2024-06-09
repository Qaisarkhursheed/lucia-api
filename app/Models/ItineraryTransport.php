<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ItineraryTransport
 * 
 * @property int $id
 * @property int $transit_type_id
 * @property int $itinerary_id
 * @property float|null $price
 * @property string|null $notes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $transport_from
 * @property string|null $transport_to
 * @property Carbon $departure_datetime
 * @property Carbon $arrival_datetime
 * @property string|null $vehicle
 * @property int $booking_category_id
 * @property string|null $custom_header_title
 * @property string|null $google_calendar_event_id
 * @property int $sorting_rank
 * 
 * @property BookingCategory $booking_category
 * @property Itinerary $itinerary
 * @property TransitType $transit_type
 * @property Collection|TransportPassenger[] $transport_passengers
 * @property Collection|TransportPicture[] $transport_pictures
 * @property TransportSupplier $transport_supplier
 *
 * @package App\Models
 */
class ItineraryTransport extends ModelBase
{
	protected $table = 'itinerary_transports';

	protected $casts = [
		'transit_type_id' => 'int',
		'itinerary_id' => 'int',
		'price' => 'float',
		'booking_category_id' => 'int',
		'sorting_rank' => 'int'
	];

	protected $dates = [
		'departure_datetime',
		'arrival_datetime'
	];

	protected $fillable = [
		'transit_type_id',
		'itinerary_id',
		'price',
		'notes',
		'transport_from',
		'transport_to',
		'departure_datetime',
		'arrival_datetime',
		'vehicle',
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

	public function transit_type()
	{
		return $this->belongsTo(TransitType::class);
	}

	public function transport_passengers()
	{
		return $this->hasMany(TransportPassenger::class);
	}

	public function transport_pictures()
	{
		return $this->hasMany(TransportPicture::class);
	}

	public function transport_supplier()
	{
		return $this->hasOne(TransportSupplier::class);
	}
}
