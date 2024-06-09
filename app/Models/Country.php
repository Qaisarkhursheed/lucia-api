<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Country
 * 
 * @property int $id
 * @property string $iso_3166_1_alpha2_code
 * @property string $description
 * 
 * @property Collection|User[] $users
 *
 * @package App\Models
 */
class Country extends ModelBase
{
	protected $table = 'countries';
	public $timestamps = false;

	protected $fillable = [
		'iso_3166_1_alpha2_code',
		'description'
	];

	public function users()
	{
		return $this->hasMany(User::class);
	}
}
