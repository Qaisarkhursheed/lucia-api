<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class BookingOcrImport
 * 
 * @property int $id
 * @property int|null $booking_id
 * @property int|null $booking_category_id
 * @property int $booking_ocr_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property BookingOcr $booking_ocr
 * @property BookingCategory|null $booking_category
 *
 * @package App\Models
 */
class BookingOcrImport extends ModelBase
{
	protected $table = 'booking_ocr_import';

	protected $casts = [
		'booking_id' => 'int',
		'booking_category_id' => 'int',
		'booking_ocr_id' => 'int'
	];

	protected $fillable = [
		'booking_id',
		'booking_category_id',
		'booking_ocr_id'
	];

	public function booking_ocr()
	{
		return $this->belongsTo(BookingOcr::class);
	}

	public function booking_category()
	{
		return $this->belongsTo(BookingCategory::class);
	}
}
