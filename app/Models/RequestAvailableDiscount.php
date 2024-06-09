<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class RequestAvailableDiscount
 * 
 * @property int $id
 * @property string $description
 * @property int $limit_to_usage_count
 * @property float $discount
 * @property float $limit_purchase_amount
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 */
class RequestAvailableDiscount extends ModelBase
{
	protected $table = 'request_available_discount';

	protected $casts = [
		'limit_to_usage_count' => 'int',
		'discount' => 'float',
		'limit_purchase_amount' => 'float',
		'is_active' => 'bool'
	];

	protected $fillable = [
		'description',
		'limit_to_usage_count',
		'discount',
		'limit_purchase_amount',
		'is_active'
	];
}
