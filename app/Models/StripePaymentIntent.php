<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class StripePaymentIntent
 * 
 * @property int $id
 * @property int $user_id
 * @property bool $succeeded
 * @property array|null $stripe_response
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property User $user
 * @property Collection|AdvisorRequestPayment[] $advisor_request_payments
 *
 * @package App\Models
 */
class StripePaymentIntent extends ModelBase
{
	protected $table = 'stripe_payment_intent';

	protected $casts = [
		'user_id' => 'int',
		'succeeded' => 'bool',
		'stripe_response' => 'json'
	];

	protected $fillable = [
		'user_id',
		'succeeded',
		'stripe_response'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function advisor_request_payments()
	{
		return $this->hasMany(AdvisorRequestPayment::class);
	}
}
