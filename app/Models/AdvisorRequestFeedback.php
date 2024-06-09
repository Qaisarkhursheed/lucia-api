<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class AdvisorRequestFeedback
 * 
 * @property int $id
 * @property float $average_rating
 * @property int $advisor_request_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property AdvisorRequest $advisor_request
 * @property Collection|AdvisorRequestFeedbackRating[] $advisor_request_feedback_ratings
 *
 * @package App\Models
 */
class AdvisorRequestFeedback extends ModelBase
{
	protected $table = 'advisor_request_feedback';

	protected $casts = [
		'average_rating' => 'float',
		'advisor_request_id' => 'int'
	];

	protected $fillable = [
		'average_rating',
		'advisor_request_id'
	];

	public function advisor_request()
	{
		return $this->belongsTo(AdvisorRequest::class);
	}

	public function advisor_request_feedback_ratings()
	{
		return $this->hasMany(AdvisorRequestFeedbackRating::class);
	}
}
