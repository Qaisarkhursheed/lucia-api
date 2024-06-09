<?php

namespace App\Http\Controllers\Agent\Itinerary;

use App\Http\Responses\ExpectionFailedResponse;
use App\Http\Responses\OkResponse;
use App\Models\BookingOcrImport;
use App\ModelsExtended\BookingOcr;
use App\ModelsExtended\ModelBase;
use App\ModelsExtended\OcrStatus;
use App\Repositories\TextractReader\AwsOcrQueues;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @property BookingOcr $model
 */
class ImportBookingOcrController extends ItineraryItemsController
{
    public function __construct()
    {
        parent::__construct( "import_ocr_id" );
    }

    /**
     * @inheritDoc
     */
    public function getDataQuery(): Builder
    {
        return BookingOcr::query()
            ->whereHas( "itinerary" , function ( Builder $builder ) {
                $builder->where( "itinerary.id", $this->getItineraryId() );
            });
    }

    public function getCommonRules()
    {
        return [
            'booking_document' => 'required|array|min:1',
            'booking_document.*' => 'required|file|mimes:pdf|max:10000'
        ];
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function store( Request $request)
    {
        $this->validatedRules( $this->getCommonRules() );

        foreach ( $request->file( 'booking_document' ) as $booking_document )
        {
            $queues = new AwsOcrQueues();
            $queues->uploadFileToS3($booking_document->hashName(), $booking_document->getContent());
            $this->model = $queues->addFileToQueue( auth()->id(),
                $this->getItineraryId(),
                $booking_document->hashName(),
                $booking_document->getClientOriginalName()
            );
        }

        return $this->status();
    }

    /**
     * @return ExpectionFailedResponse|OkResponse
     * @throws Exception
     */
    public function status()
    {
        switch ($this->model->ocr_status_id)
        {
            case OcrStatus::QUEUED:
            case OcrStatus::INITIALIZED:
            case OcrStatus::RECOGNIZING:
            case OcrStatus::COMPLETED_RECOGNITION:
            case OcrStatus::IMPORTING:
            return new OkResponse([
                "import_ocr_id" => $this->model->id,
                "document_url" => $this->model->document_url,
                "percentage_completed" => $this->model->percentage_completed,
                "status" => $this->model->ocr_status->description
            ]);

            case OcrStatus::FAILED_RECOGNITION:
            case OcrStatus::FAILED_IMPORTATION:
            return new ExpectionFailedResponse(message("We are sorry the file uploaded could not be processed. Our technical team has been informed about the issue."));

            case OcrStatus::IMPORTED:
                return new OkResponse([
                    "import_ocr_id" => $this->model->id,
                    "document_url" => $this->model->document_url,
                    "percentage_completed" => $this->model->percentage_completed,
                    "status" => $this->model->ocr_status->description,
                    "data" => $this->buildBookingData(),
                ]);
        }

        throw new Exception("Unknown status!");
    }

    /**
     * @return array
     */
    private function buildBookingData(): array
    {
        try
        {
            return $this->model->booking_ocr_imports
                ->map(
                    fn( BookingOcrImport $import)
                    => ModelBase::getBookingByCategoryId( $this->model->itinerary_id, $import->booking_category_id, $import->booking_id )
                        ->formatForSharing()
                )->toArray();
        }catch (Exception $exception){
            // Exception could occur here if the booking is deleted or itinerary and can't reference it.
            return [];
        }
    }
}
