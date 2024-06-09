<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Traveller
 * 
 * @property int $id
 * @property string $name
 * @property string|null $phone
 * @property string|null $abstract_note
 * @property int $created_by_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $birthday
 * @property string|null $address
 * @property string|null $image_relative_url
 * 
 * @property User $user
 * @property Collection|Itinerary[] $itineraries
 * @property Collection|TravellerDocument[] $traveller_documents
 * @property Collection|TravellerEmail[] $traveller_emails
 *
 * @package App\Models
 */
class Traveller extends ModelBase
{
	protected $table = 'traveller';

	protected $casts = [
		'created_by_id' => 'int'
	];

	protected $dates = [
		'birthday'
	];

	protected $fillable = [
		'name',
		'phone',
		'abstract_note',
		'created_by_id',
		'birthday',
		'address',
		'image_relative_url'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'created_by_id');
	}

	public function itineraries()
	{
		return $this->hasMany(Itinerary::class);
	}

	public function traveller_documents()
	{
		return $this->hasMany(TravellerDocument::class);
	}

	public function traveller_emails()
	{
		return $this->hasMany(TravellerEmail::class);
	}
}
