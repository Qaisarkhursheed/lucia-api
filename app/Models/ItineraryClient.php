<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ItineraryClient
 * 
 * @property int $id
 * @property string $name
 * @property string|null $phone
 * @property int $itinerary_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property Itinerary $itinerary
 * @property Collection|ClientEmail[] $client_emails
 *
 * @package App\Models
 */
class ItineraryClient extends ModelBase
{
	protected $table = 'itinerary_client';

	protected $casts = [
		'itinerary_id' => 'int'
	];

	protected $fillable = [
		'name',
		'phone',
		'itinerary_id'
	];

	public function itinerary()
	{
		return $this->belongsTo(Itinerary::class);
	}

	public function client_emails()
	{
		return $this->hasMany(ClientEmail::class);
	}
}
