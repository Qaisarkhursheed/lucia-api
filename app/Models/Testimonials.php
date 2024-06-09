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
class Testimonials extends ModelBase
{
	protected $table = 'testimonials';
	
	protected $fillable = [
		'first_name',
		'last_name',
		'bussines_name',
		'user_id',
		'message'

	];

	public function users()
	{
		return $this->hasOne(User::class);
	}
}
