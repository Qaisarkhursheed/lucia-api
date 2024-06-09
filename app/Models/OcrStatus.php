<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class OcrStatus
 * 
 * @property int $id
 * @property string $description
 * 
 * @property Collection|BookingOcr[] $booking_ocrs
 * @property Collection|BookingOcrRecognitionLog[] $booking_ocr_recognition_logs
 *
 * @package App\Models
 */
class OcrStatus extends ModelBase
{
	protected $table = 'ocr_status';
	public $timestamps = false;

	protected $fillable = [
		'description'
	];

	public function booking_ocrs()
	{
		return $this->hasMany(BookingOcr::class);
	}

	public function booking_ocr_recognition_logs()
	{
		return $this->hasMany(BookingOcrRecognitionLog::class);
	}
}
