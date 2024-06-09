<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class UserSession
 * 
 * @property int $id
 * @property int $user_id
 * @property int $role_id
 * @property int $allocated_minutes
 * @property string $token
 * @property Carbon $expiry_date_time
 * @property string $ip_address
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property Role $role
 * @property User $user
 *
 * @package App\Models
 */
class UserSession extends ModelBase
{
	protected $table = 'user_session';

	protected $casts = [
		'user_id' => 'int',
		'role_id' => 'int',
		'allocated_minutes' => 'int'
	];

	protected $dates = [
		'expiry_date_time'
	];

	protected $hidden = [
		'token'
	];

	protected $fillable = [
		'user_id',
		'role_id',
		'allocated_minutes',
		'token',
		'expiry_date_time',
		'ip_address'
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
