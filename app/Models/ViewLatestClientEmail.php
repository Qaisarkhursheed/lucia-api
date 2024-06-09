<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class ViewLatestClientEmail
 * 
 * @property int $id
 * @property string $email
 * @property int $itinerary_client_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 */
class ViewLatestClientEmail extends ModelBase
{
	protected $table = 'view_latest_client_emails';
	public $incrementing = false;

	protected $casts = [
		'id' => 'int',
		'itinerary_client_id' => 'int'
	];

	protected $fillable = [
		'id',
		'email',
		'itinerary_client_id'
	];
}
