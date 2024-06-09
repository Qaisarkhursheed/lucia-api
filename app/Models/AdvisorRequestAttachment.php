<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class AdvisorRequestAttachment
 * 
 * @property int $id
 * @property string $document_relative_url
 * @property int $advisor_request_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $name
 * 
 * @property AdvisorRequest $advisor_request
 *
 * @package App\Models
 */
class AdvisorRequestAttachment extends ModelBase
{
	protected $table = 'advisor_request_attachment';

	protected $casts = [
		'advisor_request_id' => 'int'
	];

	protected $fillable = [
		'document_relative_url',
		'advisor_request_id',
		'name'
	];

	public function advisor_request()
	{
		return $this->belongsTo(AdvisorRequest::class);
	}
}
