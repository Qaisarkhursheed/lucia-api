<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class AdvisorRequestStatus
 * 
 * @property int $id
 * @property string $description
 * 
 * @property Collection|AdvisorRequest[] $advisor_requests
 *
 * @package App\Models
 */
class AdvisorRequestStatus extends ModelBase
{
	protected $table = 'advisor_request_status';
	public $timestamps = false;

	protected $fillable = [
		'description'
	];

	public function advisor_requests()
	{
		return $this->hasMany(AdvisorRequest::class);
	}
}
