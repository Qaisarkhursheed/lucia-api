<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;

/**
 * Class BeddingType
 * 
 * @property int $id
 * @property string $description
 * @property bool $is_active
 * @property int $sort_order
 *
 * @package App\Models
 */
class BeddingType extends ModelBase
{
	protected $table = 'bedding_types';
	public $timestamps = false;

	protected $casts = [
		'is_active' => 'bool',
		'sort_order' => 'int'
	];

	protected $fillable = [
		'description',
		'is_active',
		'sort_order'
	];
}
