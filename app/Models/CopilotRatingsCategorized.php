<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;

/**
 * Class CopilotRatingsCategorized
 * 
 * @property int $copilot_id
 * @property int $review_count
 * @property int $average_rating
 *
 * @package App\Models
 */
class CopilotRatingsCategorized extends ModelBase
{
	protected $table = 'copilot_ratings_categorized';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'copilot_id' => 'int',
		'review_count' => 'int',
		'average_rating' => 'int'
	];

	protected $fillable = [
		'copilot_id',
		'review_count',
		'average_rating'
	];
}
