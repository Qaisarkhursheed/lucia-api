<?php

namespace App\Http\Controllers\Agent\Itinerary;

use App\Exceptions\RecordNotFoundException;
use App\Http\Responses\OkResponse;
use App\ModelsExtended\Itinerary;
use App\ModelsExtended\ItineraryDocument;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * @property ItineraryDocument $model
 */
class DocumentController extends ItineraryItemsController
{
    protected int $MAXIMUM_DOCUMENT = 20;

    public function __construct()
    {
        parent::__construct( "document_id" );
    }

    public function getDataQuery(): Builder
    {
        return  $this->limitBookingToItineraryOwner( ItineraryDocument::query())
            ->where( "itinerary_id", $this->getItineraryId() );
    }

    public function getCommonRules()
    {
        return [
            'document' => 'required|array|min:1|max:'. $this->MAXIMUM_DOCUMENT,    // 20MB
            'document.*' => 'file|mimes:pdf|max:20000',    // 20MB
        ];
    }

    public function fetch()
    {
        return $this->model->presentForDev();
    }

    public function fetchAll()
    {
        return parent::fetchAll()->map->presentForDev();
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function store( Request $request )
    {
        $this->validatedRules( $this->getCommonRules() );

        return $this->runInALock( sprintf( 'uploading-document-%d', $this->getItineraryId() )  ,
            function ( ) use( $request ){
                $baseModel = $this->getItinerary();

                if( $baseModel->itinerary_documents->count() === $this->MAXIMUM_DOCUMENT )
                    throw new \Exception( "You have reached the maximum upload limit of " . $this->MAXIMUM_DOCUMENT );

                if( $baseModel->itinerary_documents->count() + $request->files->count() > $this->MAXIMUM_DOCUMENT )
                    throw new \Exception( "This upload will overflow maximum upload limit of " . $this->MAXIMUM_DOCUMENT );

                foreach ( $request->file('document') as $document )
                    $this->storeDocument($document, $baseModel);

                return $this->fetchAll();
            });
    }

    /**
     * Deletes the loaded model
     *
     * @return OkResponse
     * @throws RecordNotFoundException
     */
    public function delete()
    {
        Storage::cloud()->delete($this->model->document_relative_url);

        return parent::delete();
    }

    /**
     * @param UploadedFile $document
     * @param Itinerary $baseModel
     * @return void
     */
    private function storeDocument(UploadedFile $document, Itinerary $baseModel): void
    {
        $document_url =  ItineraryDocument::generateRelativePath( $document,  $baseModel );

        Storage::cloud()->put( $document_url, $document->getContent() );

        $baseModel->itinerary_documents()->create(
            [
                'name' => $document->getClientOriginalName(),
                'document_relative_url' => $document_url
            ]
        );
    }
}
