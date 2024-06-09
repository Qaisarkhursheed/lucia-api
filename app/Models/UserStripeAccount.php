<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class UserStripeAccount
 * 
 * @property int $id
 * @property int $user_id
 * @property array|null $stripe_customer
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property array|null $stripe_connect_account
 * @property bool $connect_boarding_completed
 * 
 * @property User $user
 *
 * @package App\Models
 */
class UserStripeAccount extends ModelBase
{
	protected $table = 'user_stripe_account';

	protected $casts = [
		'user_id' => 'int',
		'stripe_customer' => 'json',
		'stripe_connect_account' => 'json',
		'connect_boarding_completed' => 'bool'
	];

	protected $fillable = [
		'user_id',
		'stripe_customer',
		'stripe_connect_account',
		'connect_boarding_completed'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
