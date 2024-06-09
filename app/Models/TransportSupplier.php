<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class TransportSupplier
 * 
 * @property int $id
 * @property int $itinerary_transport_id
 * @property string $name
 * @property string|null $address
 * @property string|null $phone
 * @property string|null $website
 * @property string|null $email
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property bool $save_to_library
 * @property string|null $description
 * 
 * @property ItineraryTransport $itinerary_transport
 *
 * @package App\Models
 */
class TransportSupplier extends ModelBase
{
	protected $table = 'transport_suppliers';

	protected $casts = [
		'itinerary_transport_id' => 'int',
		'save_to_library' => 'bool'
	];

	protected $fillable = [
		'itinerary_transport_id',
		'name',
		'address',
		'phone',
		'website',
		'email',
		'save_to_library',
		'description'
	];

	public function itinerary_transport()
	{
		return $this->belongsTo(ItineraryTransport::class);
	}
}
