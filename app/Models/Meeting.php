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
 * @property Collection|Meeting[] $meetings
 *
 * @package App\Models
 */
class Meeting extends ModelBase
{
	protected $table = 'meetings';
	public $timestamps = true;

	protected $fillable = [
        'user_id','meeting_id', 'advisor_request_id', 'topic', 'type', 'start_time', 'end_time', 'start_url', 'join_url', 'status', 'duration', 'agenda', 'pre_schedule',
	];
    protected $guarded = ['*'];

}
