<?php

namespace App\Http\Controllers\Enhancers;

use App\Exceptions\RecordNotFoundException;
use App\Http\Responses\OkResponse;
use App\ModelsExtended\Interfaces\IHasImageUrlInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Nette\NotImplementedException;

trait CRUDEnabledTraitController
{
    use HasRouteModelControllerTrait;

    /**
     * @return array
     */
    public function getCommonRules()
    {
        return [];
    }

    /**
     * Return all records in data query
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function fetchAll()
    {
        return $this->getDataQuery()->get();
    }

    /**
     * Creates new resource / model
     *
     * @param Request $request
     * @return mixed
     * @throws ValidationException
     */
    abstract public function store( Request $request );

    /**
     * Update loaded resource / model
     *
     * @param Request $request
     * @throws NotImplementedException
     * @throws ValidationException
     */
    public function update( Request $request ){
        $this->model->update(  $this->validatedRules($this->getCommonRules()) );
        return $this->fetch();
    }

    /**
     * Deletes the loaded model
     *
     * @return OkResponse
     * @throws RecordNotFoundException
     */
    public function delete( )
    {
        if( $this->model instanceof IHasImageUrlInterface && $this->model->image_url  )
            Storage::cloud()->delete( $this->model->getImageUrlStorageRelativePath() );

        // adding first, so it can return model that will call events
        $this->model->newQuery()->where( $this->recordIdentifier, $this->routeParameterValue )
            ->first()
            ->delete();

        return new OkResponse( );
    }
}
