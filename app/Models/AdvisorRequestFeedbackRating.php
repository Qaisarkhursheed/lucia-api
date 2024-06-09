<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class AdvisorRequestFeedbackRating
 * 
 * @property int $id
 * @property float $rating
 * @property int $advisor_request_feedback_id
 * @property int $feedback_topic_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property AdvisorRequestFeedback $advisor_request_feedback
 * @property FeedbackTopic $feedback_topic
 *
 * @package App\Models
 */
class AdvisorRequestFeedbackRating extends ModelBase
{
	protected $table = 'advisor_request_feedback_rating';

	protected $casts = [
		'rating' => 'float',
		'advisor_request_feedback_id' => 'int',
		'feedback_topic_id' => 'int'
	];

	protected $fillable = [
		'rating',
		'advisor_request_feedback_id',
		'feedback_topic_id'
	];

	public function advisor_request_feedback()
	{
		return $this->belongsTo(AdvisorRequestFeedback::class);
	}

	public function feedback_topic()
	{
		return $this->belongsTo(FeedbackTopic::class);
	}
}
