<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class UserNote
 * 
 * @property int $id
 * @property string $title
 * @property int $created_by_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $notes
 * 
 * @property User $user
 *
 * @package App\Models
 */
class UserNote extends ModelBase
{
	protected $table = 'user_notes';

	protected $casts = [
		'created_by_id' => 'int'
	];

	protected $fillable = [
		'title',
		'created_by_id',
		'notes'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'created_by_id');
	}
}
