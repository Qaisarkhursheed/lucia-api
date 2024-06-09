<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class AccountStatus
 * 
 * @property int $id
 * @property string $description
 * 
 * @property Collection|User[] $users
 *
 * @package App\Models
 */
class AccountStatus extends ModelBase
{
	protected $table = 'account_status';
	public $timestamps = false;

	protected $fillable = [
		'description'
	];

	public function users()
	{
		return $this->hasMany(User::class);
	}
}
