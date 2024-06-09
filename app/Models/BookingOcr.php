<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\ModelsExtended\ModelBase;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class BookingOcr
 * 
 * @property int $id
 * @property string $file_name
 * @property string $s3_object_path
 * @property string|null $document_model_type
 * @property int $ocr_status_id
 * @property int|null $itinerary_id
 * @property int $created_by_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $recognition_hash_key
 * 
 * @property Itinerary|null $itinerary
 * @property User $user
 * @property OcrStatus $ocr_status
 * @property Collection|BookingOcrImport[] $booking_ocr_imports
 * @property Collection|BookingOcrImportationLog[] $booking_ocr_importation_logs
 * @property Collection|BookingOcrRecognitionLog[] $booking_ocr_recognition_logs
 *
 * @package App\Models
 */
class BookingOcr extends ModelBase
{
	protected $table = 'booking_ocr';

	protected $casts = [
		'ocr_status_id' => 'int',
		'itinerary_id' => 'int',
		'created_by_id' => 'int'
	];

	protected $fillable = [
		'file_name',
		's3_object_path',
		'document_model_type',
		'ocr_status_id',
		'itinerary_id',
		'created_by_id',
		'recognition_hash_key'
	];

	public function itinerary()
	{
		return $this->belongsTo(Itinerary::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'created_by_id');
	}

	public function ocr_status()
	{
		return $this->belongsTo(OcrStatus::class);
	}

	public function booking_ocr_imports()
	{
		return $this->hasMany(BookingOcrImport::class);
	}

	public function booking_ocr_importation_logs()
	{
		return $this->hasMany(BookingOcrImportationLog::class);
	}

	public function booking_ocr_recognition_logs()
	{
		return $this->hasMany(BookingOcrRecognitionLog::class);
	}
}
