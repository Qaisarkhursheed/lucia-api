<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class FavoriteCopilot
 *
 * @property int $id
 * @property int $copilot_id
 * @property int $client_id
 *
 * @package App\Models
 */
class FavoriteCopilot extends ModelBase
{
	protected $table = 'favorite_copilots';

	protected $fillable = [
		'copilot_id',
		'client_id',
	];
}
