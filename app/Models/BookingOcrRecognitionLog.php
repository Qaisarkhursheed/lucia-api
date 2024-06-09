<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;

/**
 * Class BookingOcrRecognitionLog
 * 
 * @property int $id
 * @property string $function_name
 * @property string|null $message
 * @property array|null $api_response
 * @property int $ocr_status_id
 * @property int $booking_ocr_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property BookingOcr $booking_ocr
 * @property OcrStatus $ocr_status
 *
 * @package App\Models
 */
class BookingOcrRecognitionLog extends ModelBase
{
	protected $table = 'booking_ocr_recognition_log';

	protected $casts = [
		'api_response' => 'json',
		'ocr_status_id' => 'int',
		'booking_ocr_id' => 'int'
	];

	protected $fillable = [
		'function_name',
		'message',
		'api_response',
		'ocr_status_id',
		'booking_ocr_id'
	];

	public function booking_ocr()
	{
		return $this->belongsTo(BookingOcr::class);
	}

	public function ocr_status()
	{
		return $this->belongsTo(OcrStatus::class);
	}
}
