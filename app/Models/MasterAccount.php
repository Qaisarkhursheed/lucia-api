<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class MasterAccount
 * 
 * @property int $id
 * @property int $user_id
 * @property string|null $title
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $created_by_id
 * 
 * @property User $user
 * @property Collection|MasterSubAccount[] $master_sub_accounts
 *
 * @package App\Models
 */
class MasterAccount extends ModelBase
{
	protected $table = 'master_account';

	protected $casts = [
		'user_id' => 'int',
		'created_by_id' => 'int'
	];

	protected $fillable = [
		'user_id',
		'title',
		'created_by_id'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function master_sub_accounts()
	{
		return $this->hasMany(MasterSubAccount::class);
	}
}
