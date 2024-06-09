<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AdvisorRequestArchived extends Model
{
    protected $table = 'advisor_request_archived';

	protected $casts = [
		'copilot_id' => 'int',
		'advisor_request_id' => 'int',
	];

	protected $fillable = [
		'copilot_id',
		'advisor_request_id'
	];

    public function copilot()
	{
		return $this->hasOne(CopilotInfo::class, 'copilot_id');
	}

    public function request()
	{
		return $this->hasOne(AdvisorRequest::class, 'advisor_request_id');
	}
}
