<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;

/**
 * Class Priority
 * 
 * @property int $id
 * @property string $description
 *
 * @package App\Models
 */
class Priority extends ModelBase
{
	protected $table = 'priorities';
	public $timestamps = false;

	protected $fillable = [
		'description'
	];
}
