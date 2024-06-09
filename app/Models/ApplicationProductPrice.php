<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class ApplicationProductPrice
 * 
 * @property int $id
 * @property float $unit_amount
 * @property string $recurring
 * @property int $application_product_id
 * @property string|null $stripe_price_id
 * @property array|null $stripe_price
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $description
 * 
 * @property ApplicationProduct $application_product
 *
 * @package App\Models
 */
class ApplicationProductPrice extends ModelBase
{
	protected $table = 'application_product_prices';

	protected $casts = [
		'unit_amount' => 'float',
		'application_product_id' => 'int',
		'stripe_price' => 'json'
	];

	protected $fillable = [
		'unit_amount',
		'recurring',
		'application_product_id',
		'stripe_price_id',
		'stripe_price',
		'description'
	];

	public function application_product()
	{
		return $this->belongsTo(ApplicationProduct::class);
	}
}
