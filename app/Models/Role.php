<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Role
 * 
 * @property int $id
 * @property string $description
 * 
 * @property Collection|StripeSubscriptionHistory[] $stripe_subscription_histories
 * @property Collection|User[] $users
 * @property Collection|UserSession[] $user_sessions
 *
 * @package App\Models
 */
class Role extends ModelBase
{
	protected $table = 'roles';
	public $timestamps = false;

	protected $fillable = [
		'description'
	];

	public function stripe_subscription_histories()
	{
		return $this->hasMany(StripeSubscriptionHistory::class);
	}

	public function users()
	{
		return $this->belongsToMany(User::class, 'user_role')
					->withPivot('id', 'stripe_subscription', 'has_valid_license')
					->withTimestamps();
	}

	public function user_sessions()
	{
		return $this->hasMany(UserSession::class);
	}
}
