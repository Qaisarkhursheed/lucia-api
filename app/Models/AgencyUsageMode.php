<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class AgencyUsageMode
 * 
 * @property int $id
 * @property string $description
 * @property string|null $notes
 * 
 * @property Collection|User[] $users
 *
 * @package App\Models
 */
class AgencyUsageMode extends ModelBase
{
	protected $table = 'agency_usage_mode';
	public $timestamps = false;

	protected $fillable = [
		'description',
		'notes'
	];

	public function users()
	{
		return $this->hasMany(User::class);
	}
}
