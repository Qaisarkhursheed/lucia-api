<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class AdvisorRequestTask
 *
 * @property int $id
 * @property string|null $explanation
 * @property int $advisor_request_type_id
 * @property int $advisor_request_id
 * @property bool $completed
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property float $amount
 *
 * @property AdvisorRequest $advisor_request
 * @property AdvisorRequestType $advisor_request_type
 *
 * @package App\Models
 */
class AdvisorRequestTask extends ModelBase
{
	protected $table = 'advisor_request_task';

	protected $casts = [
		'advisor_request_type_id' => 'int',
		'advisor_request_id' => 'int',
		'completed' => 'bool',
		'amount' => 'float'
	];

	protected $fillable = [
		'explanation',
		'advisor_request_id',
		'completed',
		'amount',
		'title',
		'categories'
	];

	public function advisor_request()
	{
		return $this->belongsTo(AdvisorRequest::class);
	}

	public function advisor_request_type()
	{
		return $this->belongsTo(AdvisorRequestType::class);
	}
}
