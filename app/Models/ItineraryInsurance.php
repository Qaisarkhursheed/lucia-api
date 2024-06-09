<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ItineraryInsurance
 * 
 * @property int $id
 * @property int $itinerary_id
 * @property float|null $price
 * @property string|null $payment
 * @property string|null $company
 * @property string|null $confirmation_reference
 * @property Carbon|null $effective_date
 * @property string|null $policy_type
 * @property string|null $cancel_policy
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
 * @property Collection|InsurancePicture[] $insurance_pictures
 * @property InsuranceSupplier $insurance_supplier
 *
 * @package App\Models
 */
class ItineraryInsurance extends ModelBase
{
	protected $table = 'itinerary_insurances';

	protected $casts = [
		'itinerary_id' => 'int',
		'price' => 'float',
		'booking_category_id' => 'int',
		'sorting_rank' => 'int'
	];

	protected $dates = [
		'effective_date'
	];

	protected $fillable = [
		'itinerary_id',
		'price',
		'payment',
		'company',
		'confirmation_reference',
		'effective_date',
		'policy_type',
		'cancel_policy',
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

	public function insurance_pictures()
	{
		return $this->hasMany(InsurancePicture::class);
	}

	public function insurance_supplier()
	{
		return $this->hasOne(InsuranceSupplier::class);
	}
}
