<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class AdvisorRequestActivity
 *
 * @property int $id
 * @property string $details
 * @property int $advisor_request_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property AdvisorRequest $advisor_request
 *
 * @package App\Models
 */
class AdvisorRequestActivity extends ModelBase
{
	protected $table = 'advisor_request_activity';

	protected $casts = [
		'advisor_request_id' => 'int'
	];

	protected $fillable = [
		'details',
		'advisor_request_id','type','receiver_id'
	];

	public function advisor_request()
	{
		return $this->belongsTo(AdvisorRequest::class);
	}
}
