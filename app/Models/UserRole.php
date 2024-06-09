<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class UserRole
 * 
 * @property int $id
 * @property int $user_id
 * @property int $role_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property array|null $stripe_subscription
 * @property bool $has_valid_license
 * 
 * @property Role $role
 * @property User $user
 *
 * @package App\Models
 */
class UserRole extends ModelBase
{
	protected $table = 'user_role';

	protected $casts = [
		'user_id' => 'int',
		'role_id' => 'int',
		'stripe_subscription' => 'json',
		'has_valid_license' => 'bool'
	];

	protected $fillable = [
		'user_id',
		'role_id',
		'stripe_subscription',
		'has_valid_license'
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
