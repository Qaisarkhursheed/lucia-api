<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class AdvisorRequestType
 * 
 * @property int $id
 * @property float $amount
 * @property string $description
 * @property string|null $copilot_remarks
 * @property string|null $explanation
 * @property bool $is_active
 * 
 * @property Collection|AdvisorRequestTask[] $advisor_request_tasks
 * @property Collection|CopilotDuty[] $copilot_duties
 *
 * @package App\Models
 */
class AdvisorRequestType extends ModelBase
{
	protected $table = 'advisor_request_type';
	public $timestamps = false;

	protected $casts = [
		'amount' => 'float',
		'is_active' => 'bool'
	];

	protected $fillable = [
		'amount',
		'description',
		'copilot_remarks',
		'explanation',
		'is_active'
	];

	public function advisor_request_tasks()
	{
		return $this->hasMany(AdvisorRequestTask::class);
	}

	public function copilot_duties()
	{
		return $this->hasMany(CopilotDuty::class);
	}
}
