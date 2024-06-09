<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class BookingCategory
 * 
 * @property int $id
 * @property string $description
 * 
 * @property Collection|BookingOcrImport[] $booking_ocr_imports
 * @property Collection|ItineraryConcierge[] $itinerary_concierges
 * @property Collection|ItineraryCruise[] $itinerary_cruises
 * @property Collection|ItineraryDivider[] $itinerary_dividers
 * @property Collection|ItineraryFlight[] $itinerary_flights
 * @property Collection|ItineraryHeader[] $itinerary_headers
 * @property Collection|ItineraryHotel[] $itinerary_hotels
 * @property Collection|ItineraryInsurance[] $itinerary_insurances
 * @property Collection|ItineraryOther[] $itinerary_others
 * @property Collection|ItineraryTour[] $itinerary_tours
 * @property Collection|ItineraryTransport[] $itinerary_transports
 * @property Collection|ServiceSupplier[] $service_suppliers
 *
 * @package App\Models
 */
class BookingCategory extends ModelBase
{
	protected $table = 'booking_category';
	public $timestamps = false;

	protected $fillable = [
		'description'
	];

	public function booking_ocr_imports()
	{
		return $this->hasMany(BookingOcrImport::class);
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

	public function itinerary_tours()
	{
		return $this->hasMany(ItineraryTour::class);
	}

	public function itinerary_transports()
	{
		return $this->hasMany(ItineraryTransport::class);
	}

	public function service_suppliers()
	{
		return $this->hasMany(ServiceSupplier::class);
	}
}
