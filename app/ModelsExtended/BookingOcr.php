<?php

namespace App\ModelsExtended;

use App\Mail\Agent\Itinerary\ImportOCRResultMail;
use App\Models\BookingOcrRecognitionLog;
use App\Repositories\TextractReader\AwsOcrQueues;
use Illuminate\Support\Facades\Mail;

/**
 * @property string $document_url
 * @property float $percentage_completed
 * @property BookingOcrRecognitionLog $completed_ocr_recognition_log
 */
class BookingOcr extends \App\Models\BookingOcr
{
    protected $appends = [ "document_url", "percentage_completed" ];

    public function completed_ocr_recognition_log()
    {
        return $this->hasOne(BookingOcrRecognitionLog::class)
            ->where( "ocr_status_id", OcrStatus::COMPLETED_RECOGNITION );
    }

    /**
     * @param int $id
     * @return BookingOcr
     */
    public static function getById( int $id )
    {
        return self::find($id);
    }

    /**
     * @return string
     */
    public function getDocumentUrlAttribute(): string
    {
        return (new AwsOcrQueues())->fileUrl( $this->s3_object_path );
    }

    /**
     * @return float
     */
    public function getPercentageCompletedAttribute(): float
    {
        switch ($this->ocr_status_id)
        {
            case OcrStatus::QUEUED:
                return 5;
            case OcrStatus::INITIALIZED:
                return 10;
            case OcrStatus::RECOGNIZING:
                return 40;
            case OcrStatus::COMPLETED_RECOGNITION:
                return 70;
            case OcrStatus::IMPORTING:
                return 90;

            case OcrStatus::FAILED_RECOGNITION:
            case OcrStatus::FAILED_IMPORTATION:
            case OcrStatus::IMPORTED:
                return 100;

            default:
                return 0;
        }
    }

    /**
     * This will delete any trace of this
     *
     * @return bool|null
     */
    public function cleanUp(): ?bool
    {
        if( (new AwsOcrQueues())->deleteFile( $this->s3_object_path ) )
            return $this->delete();
        return false;
    }

    /**
     * @param int $ocr_status_id
     * @return $this
     */
    public function setStatus(int $ocr_status_id): BookingOcr
    {
        $this->ocr_status_id = $ocr_status_id;
        $this->update();

        // inform by email.
        if(
            $ocr_status_id === OcrStatus::IMPORTED ||
            $ocr_status_id === OcrStatus::FAILED_IMPORTATION ||
            $ocr_status_id === OcrStatus::FAILED_RECOGNITION
        ) Mail::send( new ImportOCRResultMail( $this ) );


        return $this;
    }
}
