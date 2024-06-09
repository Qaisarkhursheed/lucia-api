<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class ShipPort
 * 
 * @property int $id
 * @property string $name
 * @property int|null $ref_port_id
 * @property string|null $description
 * @property string|null $latitude
 * @property string|null $longitude
 * @property int $service_supplier_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property ServiceSupplier $service_supplier
 *
 * @package App\Models
 */
class ShipPort extends ModelBase
{
	protected $table = 'ship_ports';

	protected $casts = [
		'ref_port_id' => 'int',
		'service_supplier_id' => 'int'
	];

	protected $fillable = [
		'name',
		'ref_port_id',
		'description',
		'latitude',
		'longitude',
		'service_supplier_id'
	];

	public function service_supplier()
	{
		return $this->belongsTo(ServiceSupplier::class);
	}
}
