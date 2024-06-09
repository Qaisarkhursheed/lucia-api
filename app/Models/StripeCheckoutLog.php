<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class StripeCheckoutLog
 * 
 * @property int $id
 * @property int $user_id
 * @property string $session_id
 * @property string $redirect_url
 * @property array|null $stripe_response
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property User $user
 *
 * @package App\Models
 */
class StripeCheckoutLog extends ModelBase
{
	protected $table = 'stripe_checkout_logs';

	protected $casts = [
		'user_id' => 'int',
		'stripe_response' => 'json'
	];

	protected $fillable = [
		'user_id',
		'session_id',
		'redirect_url',
		'stripe_response'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
