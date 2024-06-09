<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class CopilotInfo
 * 
 * @property int $id
 * @property string|null $how_to_fulfill
 * @property string|null $free_time_recommendations
 * @property string|null $strengths
 * @property string|null $confidential_handling
 * @property string|null $experience
 * @property string|null $contact_references
 * @property string|null $other_info
 * @property int $copilot_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $weaknesses
 * @property string|null $bio
 * @property string|null $resume_relative_url
 * 
 * @property User $user
 *
 * @package App\Models
 */
class CopilotInfo extends ModelBase
{
	protected $table = 'copilot_info';

	protected $casts = [
		'copilot_id' => 'int'
	];

	protected $fillable = [
		'how_to_fulfill',
		'free_time_recommendations',
		'strengths',
		'confidential_handling',
		'experience',
		'contact_references',
		'other_info',
		'copilot_id',
		'weaknesses',
		'bio',
		'resume_relative_url'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'copilot_id');
	}
}
