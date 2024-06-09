<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class ServiceShip
 * 
 * @property int $id
 * @property string $name
 * @property int|null $ref_ship_id
 * @property int $service_supplier_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property ServiceSupplier $service_supplier
 *
 * @package App\Models
 */
class ServiceShip extends ModelBase
{
	protected $table = 'service_ships';

	protected $casts = [
		'ref_ship_id' => 'int',
		'service_supplier_id' => 'int'
	];

	protected $fillable = [
		'name',
		'ref_ship_id',
		'service_supplier_id'
	];

	public function service_supplier()
	{
		return $this->belongsTo(ServiceSupplier::class);
	}
}
