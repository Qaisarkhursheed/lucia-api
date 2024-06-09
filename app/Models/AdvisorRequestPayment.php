<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class AdvisorRequestPayment
 * 
 * @property int $id
 * @property int $advisor_request_id
 * @property array|null $stripe_payment_info
 * @property float $amount
 * @property int|null $stripe_payment_intent_id
 * @property string|null $stripe_charge_id
 * @property string|null $stripe_refund_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property StripePaymentIntent|null $stripe_payment_intent
 * @property AdvisorRequest $advisor_request
 *
 * @package App\Models
 */
class AdvisorRequestPayment extends ModelBase
{
	protected $table = 'advisor_request_payment';

	protected $casts = [
		'advisor_request_id' => 'int',
		'stripe_payment_info' => 'json',
		'amount' => 'float',
		'stripe_payment_intent_id' => 'int'
	];

	protected $fillable = [
		'advisor_request_id',
		'stripe_payment_info',
		'amount',
		'stripe_payment_intent_id',
		'stripe_charge_id',
		'stripe_refund_id',
		'is_lucia_payment'
	];

	public function stripe_payment_intent()
	{
		return $this->belongsTo(StripePaymentIntent::class);
	}

	public function advisor_request()
	{
		return $this->belongsTo(AdvisorRequest::class);
	}
}
