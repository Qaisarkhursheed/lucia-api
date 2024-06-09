<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class StripeSubscriptionHistory
 * 
 * @property int $id
 * @property int $user_id
 * @property array $stripe_subscription
 * @property string $status
 * @property string|null $plan_interval
 * @property float|null $amount_decimal
 * @property Carbon|null $current_period_start
 * @property Carbon|null $current_period_end
 * @property Carbon|null $start_date
 * @property Carbon|null $ended_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $role_id
 * 
 * @property Role $role
 * @property User $user
 *
 * @package App\Models
 */
class StripeSubscriptionHistory extends ModelBase
{
	protected $table = 'stripe_subscription_history';

	protected $casts = [
		'user_id' => 'int',
		'stripe_subscription' => 'json',
		'amount_decimal' => 'float',
		'role_id' => 'int'
	];

	protected $dates = [
		'current_period_start',
		'current_period_end',
		'start_date',
		'ended_at'
	];

	protected $fillable = [
		'user_id',
		'stripe_subscription',
		'status',
		'plan_interval',
		'amount_decimal',
		'current_period_start',
		'current_period_end',
		'start_date',
		'ended_at',
		'role_id'
	];

	public function role()
	{
		return $this->belongsTo(Role::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
