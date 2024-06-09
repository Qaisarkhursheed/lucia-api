<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class TravellerEmail
 * 
 * @property int $id
 * @property string $email
 * @property int $traveller_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property Traveller $traveller
 *
 * @package App\Models
 */
class TravellerEmail extends ModelBase
{
	protected $table = 'traveller_email';

	protected $casts = [
		'traveller_id' => 'int'
	];

	protected $fillable = [
		'email',
		'traveller_id'
	];

	public function traveller()
	{
		return $this->belongsTo(Traveller::class);
	}
}
