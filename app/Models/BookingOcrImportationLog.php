<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class BookingOcrImportationLog
 * 
 * @property int $id
 * @property string $function_name
 * @property string|null $log
 * @property int $booking_ocr_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property BookingOcr $booking_ocr
 *
 * @package App\Models
 */
class BookingOcrImportationLog extends ModelBase
{
	protected $table = 'booking_ocr_importation_log';

	protected $casts = [
		'booking_ocr_id' => 'int'
	];

	protected $fillable = [
		'function_name',
		'log',
		'booking_ocr_id'
	];

	public function booking_ocr()
	{
		return $this->belongsTo(BookingOcr::class);
	}
}
