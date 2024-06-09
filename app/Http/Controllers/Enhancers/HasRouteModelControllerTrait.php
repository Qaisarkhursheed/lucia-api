<?php

namespace App\Http\Controllers\Enhancers;

use App\Exceptions\RecordNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Responses\OkResponse;
use App\ModelsExtended\Interfaces\ICanCreateServiceProviderInterface;
use App\ModelsExtended\Interfaces\IReplicableEloquent;
use App\ModelsExtended\ModelBase;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends Controller
 */
trait HasRouteModelControllerTrait
{
    /**
     * Resource / Model for this controller
     * @var ModelBase | ICanCreateServiceProviderInterface | IReplicableEloquent
     */
    protected $model;

    /**
     * This is the primary key name that we will use to identify the model
     * for this class ONLY for fetching
     * @var string
     */
    protected string $recordIdentifier;

    /**
     * @var mixed
     */
    protected $routeParameterValue;

    /**
     * Initiate the loading function
     *
     * @param string $param_name
     * @param string $recordIdentifier
     * @throws RecordNotFoundException
     */
    protected function invokeLoadRouteModelFunction( string $param_name, string $recordIdentifier = "id" )
    {
        $this->recordIdentifier = $recordIdentifier;
        $this->canLoadOnResource($param_name, function ($route_param_value, bool $withRelations) {

            $this->routeParameterValue = $route_param_value;

            $this->model = $this->loadModel($route_param_value, $withRelations);
        });
    }
    /**
     * Fetches the loaded model
     *
     * @return OkResponse
     */
    public function fetch( )
    {
        if( \request( 'present' ) )
            return new OkResponse( $this->model->presentForDev() );
        return new OkResponse( $this->model );
    }

    /**
     * Get data query used to build this page

     * @return Builder
     */
    abstract public function getDataQuery(): Builder;

    /**
     * Load the model with the resource passed from route
     *
     * @param mixed $route_param_value
     * @param bool $withRelations
     * @return mixed
     * @throws RecordNotFoundException
     */
    protected function loadModel( $route_param_value, bool $withRelations = true ){
        $query = $this->getDataQuery()->where( $this->recordIdentifier, $route_param_value);

        // Set value
        $this->routeParameterValue = $route_param_value;

        if( ! $withRelations && count( $query->getEagerLoads() ) ) $query->without(  array_keys($query->getEagerLoads()) );

        $this->model = $query->first();

        if( ! $this->model ) throw new RecordNotFoundException();

        return $this->model;
    }

}
