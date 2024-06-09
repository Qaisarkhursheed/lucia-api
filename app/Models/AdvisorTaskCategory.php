<?php

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class AdvisorTaskCategory
 *
 * @property int $id
 * @property int $category_id
 * @property int $advisor_request_task_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 */
class AdvisorTaskCategory extends ModelBase
{
    protected $table = 'advisor_task_categories';

	protected $fillable = [
        'id',
		'advisor_request_task_id',
		'category_id',
	];
}
