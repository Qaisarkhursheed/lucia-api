<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class CopilotDuty
 * 
 * @property int $id
 * @property int $advisor_request_type_id
 * @property int $copilot_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property User $user
 * @property AdvisorRequestType $advisor_request_type
 *
 * @package App\Models
 */
class CopilotDuty extends ModelBase
{
	protected $table = 'copilot_duties';

	protected $casts = [
		'advisor_request_type_id' => 'int',
		'copilot_id' => 'int'
	];

	protected $fillable = [
		'advisor_request_type_id',
		'copilot_id'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'copilot_id');
	}

	public function advisor_request_type()
	{
		return $this->belongsTo(AdvisorRequestType::class);
	}
}
