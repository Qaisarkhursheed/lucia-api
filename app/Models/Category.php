<?php

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class BookingOcrRecognitionLog
 * 
 * @property int $id
 * @property string $name
 * @property int $category_type_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 */
class Category extends ModelBase
{
    protected $table = 'categories';

	protected $fillable = [
		'name',
		'category_type_id',
	];
}
