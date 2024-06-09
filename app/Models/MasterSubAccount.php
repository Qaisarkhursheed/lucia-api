<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class MasterSubAccount
 * 
 * @property int $id
 * @property int $user_id
 * @property int $master_account_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $created_by_id
 * 
 * @property User $user
 * @property MasterAccount $master_account
 *
 * @package App\Models
 */
class MasterSubAccount extends ModelBase
{
	protected $table = 'master_sub_account';

	protected $casts = [
		'user_id' => 'int',
		'master_account_id' => 'int',
		'created_by_id' => 'int'
	];

	protected $fillable = [
		'user_id',
		'master_account_id',
		'created_by_id'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function master_account()
	{
		return $this->belongsTo(MasterAccount::class);
	}
}
