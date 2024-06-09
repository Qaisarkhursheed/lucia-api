<?php

namespace App\Http\Controllers\Enhancers;


use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

/**
 * https://yajrabox.com/docs/laravel-datatables/master/engine-eloquent
 */
class YajraEloquentDataTableExtended extends \Yajra\DataTables\EloquentDataTable
{

    protected ?IYajraEloquentResultProcessorInterface $processor;

    /**
     * @inheritDoc
     */
    public function make($mDataSupport = true)
    {
        try {

            $this->prepareQuery();

            // Collection is available here
            $results   = $this->results();

            // Also, makeHidden and makeVisible are called here if exists
            // Those are methods in Eloquent Model

            // Turned to array here
            $processed = $this->processor ? $this->processor->processYajraEloquentResult( $results ) : $this->processResults($results, $mDataSupport);

            // Transform if possible the array of the processed
            // https://yajrabox.com/docs/laravel-datatables/master/response-fractal
            $data      = $this->transform($results, $processed);

            // render the array as JsonResponse
            return $this->render($data);
        } catch (Exception $exception) {
            return $this->errorResponse($exception);
        }
    }

    /**
     * @param IYajraEloquentResultProcessorInterface|null $processor
     * @param false $returnValuesCollectionOnly
     * @return JsonResponse
     * @throws Exception
     */
    public function customMake(?IYajraEloquentResultProcessorInterface $processor = null, bool $returnValuesCollectionOnly = false ): JsonResponse
    {
        $this->processor = $processor;
        return $this->make( ! $returnValuesCollectionOnly );
    }

    /**
     * @param Builder $builder
     * @return YajraEloquentDataTableExtended
     */
    public static function instance( Builder $builder ): YajraEloquentDataTableExtended
    {
        return new self( $builder );
    }
}
