<?php

namespace App\Jobs;

use App\ModelsExtended\BookingOcr;
use App\ModelsExtended\OcrStatus;
use App\Repositories\TextractReader\DocumentTypeDetector;
use Illuminate\Support\Facades\Log;

class OcrImportJob extends Job
{
    private BookingOcr $bookingOcr;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(BookingOcr $bookingOcr)
    {
        //
        $this->bookingOcr = $bookingOcr;
    }

    /**
     * @param BookingOcr $ocr
     * @return void
     * @throws \Exception
     */
    public static function processImportation(BookingOcr $ocr)
    {
        if( $ocr->ocr_status_id !== OcrStatus::IMPORTING )
            throw new \Exception("Importation can not be processed because the status is " . $ocr->ocr_status->description );

        try {

            // detect document type
            $reader = ( new DocumentTypeDetector() )->detect( $ocr );
            $ocr->document_model_type = $reader->name();

            // use document type to read bookings
            foreach ( $reader->read( $ocr->completed_ocr_recognition_log->api_response) as $booking ){
                $ocr->booking_ocr_imports()->create([
                    'booking_id' => $booking->id,
                    'booking_category_id'=> $booking->booking_category_id,
                ]);
            }

            $ocr->setStatus( OcrStatus::IMPORTED );

        }catch (\Exception $exception){
//            dd( $exception );
            $ocr->booking_ocr_importation_logs()->create([
                'function_name' => __FUNCTION__,
                'log' =>  $exception->getMessage(),
            ]);
            $ocr->setStatus( OcrStatus::FAILED_IMPORTATION );
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            self::processImportation($this->bookingOcr);
        } catch (\Exception $e) {
            Log::info( $e->getMessage() );
        }
    }
}
