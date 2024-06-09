<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class ClientEmail
 * 
 * @property int $id
 * @property string $email
 * @property int $itinerary_client_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property ItineraryClient $itinerary_client
 *
 * @package App\Models
 */
class ClientEmail extends ModelBase
{
	protected $table = 'client_emails';

	protected $casts = [
		'itinerary_client_id' => 'int'
	];

	protected $fillable = [
		'email',
		'itinerary_client_id'
	];

	public function itinerary_client()
	{
		return $this->belongsTo(ItineraryClient::class);
	}
}
