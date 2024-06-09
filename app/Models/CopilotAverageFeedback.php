<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;

/**
 * Class CopilotAverageFeedback
 * 
 * @property int $copilot_id
 * @property int $review_count
 * @property float|null $average_rating
 *
 * @package App\Models
 */
class CopilotAverageFeedback extends ModelBase
{
	protected $table = 'copilot_average_feedback';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'copilot_id' => 'int',
		'review_count' => 'int',
		'average_rating' => 'float'
	];

	protected $fillable = [
		'copilot_id',
		'review_count',
		'average_rating'
	];
}
