<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ActivityType
 *
 * @property int $id
 * @property string $description
 *
 * @property Collection|ActivityType[] $activitytypes
 *
 * @package App\Models
 */
class ActivityType extends ModelBase
{
	protected $table = 'activity_types';
	public $timestamps = true;

	protected $fillable = [
        'id',
		'description'
	];
    protected $guarded = ['*'];

    public const MESSAGE = 1;//for message
    public const ADVISOR_REQUEST = 2; // For advisor request
    public const MEETING = 3; // For FOR ZOOM MEETINGS
}
