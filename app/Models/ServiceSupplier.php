<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ServiceSupplier
 * 
 * @property int $id
 * @property string $name
 * @property string|null $address
 * @property string|null $phone
 * @property string|null $website
 * @property string|null $email
 * @property int $booking_category_id
 * @property int $created_by_id
 * @property bool $is_globally_accessible
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $description
 * @property int|null $ref_id
 * @property string|null $image_url
 * 
 * @property BookingCategory $booking_category
 * @property User $user
 * @property Collection|SavedSupplier[] $saved_suppliers
 * @property Collection|ServiceShip[] $service_ships
 * @property Collection|ServiceSuppliersPicture[] $service_suppliers_pictures
 * @property Collection|ShipPort[] $ship_ports
 *
 * @package App\Models
 */
class ServiceSupplier extends ModelBase
{
	protected $table = 'service_suppliers';

	protected $casts = [
		'booking_category_id' => 'int',
		'created_by_id' => 'int',
		'is_globally_accessible' => 'bool',
		'ref_id' => 'int'
	];

	protected $fillable = [
		'name',
		'address',
		'phone',
		'website',
		'email',
		'booking_category_id',
		'created_by_id',
		'is_globally_accessible',
		'description',
		'ref_id',
		'image_url'
	];

	public function booking_category()
	{
		return $this->belongsTo(BookingCategory::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'created_by_id');
	}

	public function saved_suppliers()
	{
		return $this->hasMany(SavedSupplier::class);
	}

	public function service_ships()
	{
		return $this->hasMany(ServiceShip::class);
	}

	public function service_suppliers_pictures()
	{
		return $this->hasMany(ServiceSuppliersPicture::class, 'service_suppliers_id');
	}

	public function ship_ports()
	{
		return $this->hasMany(ShipPort::class);
	}
}
