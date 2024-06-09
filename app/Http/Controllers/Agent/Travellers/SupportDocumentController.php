<?php

namespace App\Http\Controllers\Agent\Travellers;

use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Enhancers\CRUDEnabledController;
use App\Http\Responses\OkResponse;
use App\ModelsExtended\Traveller;
use App\ModelsExtended\TravellerDocument;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * @property TravellerDocument $model
 */
class SupportDocumentController extends CRUDEnabledController
{
    protected int $MAXIMUM_DOCUMENT = 20;

    public function __construct()
    {
        parent::__construct( "support_document_id" );
    }

    public function getDataQuery(): Builder
    {
        return TravellerDocument::query()
             ->where(function ( Builder $builder ){
                 // limit to owner if you are operating as agent
                 $builder->whereHas( "traveller" , function ( Builder $builder ) {
                     $builder->where( "traveller.created_by_id", auth()->id() );
                 });
             });
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

        return $this->runInALock( sprintf( 'uploading-document-%d', $this->getTravellerId() )  ,
            function ( ) use( $request ){
                $baseModel = Traveller::getById($this->getTravellerId());

                if( $baseModel->traveller_documents->count() === $this->MAXIMUM_DOCUMENT )
                    throw new \Exception( "You have reached the maximum upload limit of " . $this->MAXIMUM_DOCUMENT );

                if( $baseModel->traveller_documents->count() + $request->files->count() > $this->MAXIMUM_DOCUMENT )
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
     * @param Traveller $baseModel
     * @return void
     */
    private function storeDocument(UploadedFile $document, Traveller $baseModel): void
    {
        $document_url =  TravellerDocument::generateRelativePath( $document,  $baseModel );

        Storage::cloud()->put( $document_url, $document->getContent() );

        $baseModel->traveller_documents()->create(
            [
                'name' => $document->getClientOriginalName(),
                'document_relative_url' => $document_url
            ]
        );
    }


    private function getTravellerId()
    {
        return \request()->route('traveller_id');
    }
}
