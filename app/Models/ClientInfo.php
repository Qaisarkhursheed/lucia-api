<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class ClientInfo
 * 
 * @property int $id
 * @property string|null $favorite_vacation_spot
 * @property string|null $preferred_cuisine
 * @property string|null $allergies
 * @property int $user_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property User $user
 *
 * @package App\Models
 */
class ClientInfo extends ModelBase
{
	protected $table = 'client_info';

	protected $casts = [
		'user_id' => 'int'
	];

	protected $fillable = [
		'favorite_vacation_spot',
		'preferred_cuisine',
		'allergies',
		'user_id'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
