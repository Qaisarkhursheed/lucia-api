<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class TravellerDocument
 * 
 * @property int $id
 * @property int $traveller_id
 * @property string $document_relative_url
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property Traveller $traveller
 *
 * @package App\Models
 */
class TravellerDocument extends ModelBase
{
	protected $table = 'traveller_document';

	protected $casts = [
		'traveller_id' => 'int'
	];

	protected $fillable = [
		'traveller_id',
		'document_relative_url',
		'name'
	];

	public function traveller()
	{
		return $this->belongsTo(Traveller::class);
	}
}
