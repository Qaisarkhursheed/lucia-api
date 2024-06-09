<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class FeedbackTopic
 * 
 * @property int $id
 * @property string $description
 * 
 * @property Collection|AdvisorRequestFeedbackRating[] $advisor_request_feedback_ratings
 *
 * @package App\Models
 */
class FeedbackTopic extends ModelBase
{
	protected $table = 'feedback_topic';
	public $timestamps = false;

	protected $fillable = [
		'description'
	];

	public function advisor_request_feedback_ratings()
	{
		return $this->hasMany(AdvisorRequestFeedbackRating::class);
	}
}
