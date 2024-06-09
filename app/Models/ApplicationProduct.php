<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ApplicationProduct
 * 
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string|null $stripe_product_id
 * @property array|null $stripe_product
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property Collection|ApplicationProductPrice[] $application_product_prices
 *
 * @package App\Models
 */
class ApplicationProduct extends ModelBase
{
	protected $table = 'application_products';

	protected $casts = [
		'stripe_product' => 'json'
	];

	protected $fillable = [
		'name',
		'description',
		'stripe_product_id',
		'stripe_product'
	];

	public function application_product_prices()
	{
		return $this->hasMany(ApplicationProductPrice::class);
	}
}
