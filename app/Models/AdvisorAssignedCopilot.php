<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class AdvisorAssignedCopilot
 *
 * @property int $id
 * @property int $advisor_request_id
 * @property int $copilot_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property bool $is_paid
 *
 * @property User $user
 * @property AdvisorRequest $advisor_request
 *
 * @package App\Models
 */
class AdvisorAssignedCopilot extends ModelBase
{
	protected $table = 'advisor_assigned_copilot';

	protected $casts = [
		'advisor_request_id' => 'int',
		'copilot_id' => 'int',
		'is_paid' => 'bool'
	];

	protected $fillable = [
		'advisor_request_id',
		'copilot_id',
		'is_paid'
	];
    protected $with = [
		'user'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'copilot_id');
	}

	public function advisor_request()
	{
		return $this->belongsTo(AdvisorRequest::class);
	}

	public function advisor_requests()
	{
		return $this->hasOne(AdvisorRequest::class);
	}
}
