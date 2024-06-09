<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class ServiceSuppliersPicture
 * 
 * @property int $id
 * @property int $service_suppliers_id
 * @property string $image_relative_url
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property ServiceSupplier $service_supplier
 *
 * @package App\Models
 */
class ServiceSuppliersPicture extends ModelBase
{
	protected $table = 'service_suppliers_pictures';

	protected $casts = [
		'service_suppliers_id' => 'int'
	];

	protected $fillable = [
		'service_suppliers_id',
		'image_relative_url'
	];

	public function service_supplier()
	{
		return $this->belongsTo(ServiceSupplier::class, 'service_suppliers_id');
	}
}
