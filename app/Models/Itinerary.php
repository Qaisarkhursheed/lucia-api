<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Itinerary
 * 
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property Carbon $start_date
 * @property int $status_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $end_date
 * @property string|null $abstract_note
 * @property bool $show_price_on_share
 * @property string|null $deleted_at
 * @property string|null $share_itinerary_key
 * @property bool|null $mark_as_client_approved
 * @property int $traveller_id
 * @property string|null $google_calendar_event_id
 * @property int|null $currency_id
 * @property string|null $total_price
 * 
 * @property CurrencyType|null $currency_type
 * @property ItineraryStatus $itinerary_status
 * @property User $user
 * @property Traveller $traveller
 * @property Collection|AdvisorRequest[] $advisor_requests
 * @property Collection|BookingOcr[] $booking_ocrs
 * @property ItineraryClient $itinerary_client
 * @property Collection|ItineraryConcierge[] $itinerary_concierges
 * @property Collection|ItineraryCruise[] $itinerary_cruises
 * @property Collection|ItineraryDivider[] $itinerary_dividers
 * @property Collection|ItineraryDocument[] $itinerary_documents
 * @property Collection|ItineraryFlight[] $itinerary_flights
 * @property Collection|ItineraryHeader[] $itinerary_headers
 * @property Collection|ItineraryHotel[] $itinerary_hotels
 * @property Collection|ItineraryInsurance[] $itinerary_insurances
 * @property Collection|ItineraryOther[] $itinerary_others
 * @property Collection|ItineraryPassenger[] $itinerary_passengers
 * @property Collection|ItineraryPicture[] $itinerary_pictures
 * @property Collection|ItineraryTask[] $itinerary_tasks
 * @property ItineraryTheme $itinerary_theme
 * @property Collection|ItineraryTour[] $itinerary_tours
 * @property Collection|ItineraryTransport[] $itinerary_transports
 *
 * @package App\Models
 */
class Itinerary extends ModelBase
{
	use SoftDeletes;
	protected $table = 'itinerary';

	protected $casts = [
		'user_id' => 'int',
		'status_id' => 'int',
		'show_price_on_share' => 'bool',
		'mark_as_client_approved' => 'bool',
		'traveller_id' => 'int',
		'currency_id' => 'int'
	];

	protected $dates = [
		'start_date',
		'end_date'
	];

	protected $fillable = [
		'user_id',
		'title',
		'start_date',
		'status_id',
		'end_date',
		'abstract_note',
		'show_price_on_share',
		'share_itinerary_key',
		'mark_as_client_approved',
		'traveller_id',
		'google_calendar_event_id',
		'currency_id',
		'total_price'
	];

	public function currency_type()
	{
		return $this->belongsTo(CurrencyType::class, 'currency_id');
	}

	public function itinerary_status()
	{
		return $this->belongsTo(ItineraryStatus::class, 'status_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function traveller()
	{
		return $this->belongsTo(Traveller::class);
	}

	public function advisor_requests()
	{
		return $this->hasMany(AdvisorRequest::class);
	}

	public function booking_ocrs()
	{
		return $this->hasMany(BookingOcr::class);
	}

	public function itinerary_client()
	{
		return $this->hasOne(ItineraryClient::class);
	}

	public function itinerary_concierges()
	{
		return $this->hasMany(ItineraryConcierge::class);
	}

	public function itinerary_cruises()
	{
		return $this->hasMany(ItineraryCruise::class);
	}

	public function itinerary_dividers()
	{
		return $this->hasMany(ItineraryDivider::class);
	}

	public function itinerary_documents()
	{
		return $this->hasMany(ItineraryDocument::class);
	}

	public function itinerary_flights()
	{
		return $this->hasMany(ItineraryFlight::class);
	}

	public function itinerary_headers()
	{
		return $this->hasMany(ItineraryHeader::class);
	}

	public function itinerary_hotels()
	{
		return $this->hasMany(ItineraryHotel::class);
	}

	public function itinerary_insurances()
	{
		return $this->hasMany(ItineraryInsurance::class);
	}

	public function itinerary_others()
	{
		return $this->hasMany(ItineraryOther::class);
	}

	public function itinerary_passengers()
	{
		return $this->hasMany(ItineraryPassenger::class);
	}

	public function itinerary_pictures()
	{
		return $this->hasMany(ItineraryPicture::class);
	}

	public function itinerary_tasks()
	{
		return $this->hasMany(ItineraryTask::class);
	}

	public function itinerary_theme()
	{
		return $this->hasOne(ItineraryTheme::class);
	}

	public function itinerary_tours()
	{
		return $this->hasMany(ItineraryTour::class);
	}

	public function itinerary_transports()
	{
		return $this->hasMany(ItineraryTransport::class);
	}
}
