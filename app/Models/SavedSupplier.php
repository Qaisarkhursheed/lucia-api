<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class SavedSupplier
 * 
 * @property int $id
 * @property int $service_supplier_id
 * @property int $user_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property ServiceSupplier $service_supplier
 * @property User $user
 *
 * @package App\Models
 */
class SavedSupplier extends ModelBase
{
	protected $table = 'saved_supplier';

	protected $casts = [
		'service_supplier_id' => 'int',
		'user_id' => 'int'
	];

	protected $fillable = [
		'service_supplier_id',
		'user_id'
	];

	public function service_supplier()
	{
		return $this->belongsTo(ServiceSupplier::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
