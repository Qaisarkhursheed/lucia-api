<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class RegistrationAccessCode
 * 
 * @property int $id
 * @property string $code
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 */
class RegistrationAccessCode extends ModelBase
{
	protected $table = 'registration_access_codes';

	protected $fillable = [
		'code'
	];
}
