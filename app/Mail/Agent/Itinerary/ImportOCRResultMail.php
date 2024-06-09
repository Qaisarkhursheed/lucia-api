<?php

namespace App\Mail\Agent\Itinerary;

use App\ModelsExtended\BookingOcr;
use App\ModelsExtended\OcrStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ImportOCRResultMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private BookingOcr $bookingOcr;

    /**
     * Create a new message instance.
     * @param BookingOcr $bookingOcr
     */
    public function __construct(BookingOcr $bookingOcr )
    {
        if( $bookingOcr->ocr_status_id === OcrStatus::IMPORTED )
            $this->subject =  "OCR | BOOKING FILE IMPORTED";
        else
            $this->subject =  "OCR | IMPORTATION FAILED";

        $this->bookingOcr = $bookingOcr;

        // arrange the receiver so that they don't see each other
        // if more than one receiver is specified
        $this->to( $bookingOcr->user->email  );

        if( $bookingOcr->ocr_status_id === OcrStatus::FAILED_RECOGNITION )
        {
            $this->bcc("ibukun.bello@beyondimagine.co");
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view(  $this->bookingOcr->ocr_status_id === OcrStatus::IMPORTED ? "mails.agent.itinerary.import_ocr_result" : "mails.agent.itinerary.import_ocr_failed" )
            ->with( "bookingOcr" , $this->bookingOcr );
    }
}
