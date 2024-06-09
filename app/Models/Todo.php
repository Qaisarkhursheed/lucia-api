<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class Todo
 *
 * @property int $id
 * @property int $advisor_request_id
 * @property int $user_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $name
 *
 * @property User $user
 *
 * @package App\Models
 */
class Todo extends ModelBase
{
	protected $table = 'todos';

	protected $casts = [
		'created_by_id' => 'int'
	];

	protected $fillable = [
		'name',
		'advisor_request_id',
		'user_id'
	];
	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}
    public function advisor_request()
	{
		return $this->belongsTo(AdvisorRequest::class, 'advisor_request_id');
	}
}
