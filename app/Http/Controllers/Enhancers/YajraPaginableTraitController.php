<?php

namespace App\Http\Controllers\Enhancers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\EloquentDataTable;

trait YajraPaginableTraitController
{
    /**
     * @var string | null
     */
    protected $search;

    /**
     * @var string
     */
    protected string $orderByDir;

    /**
     * @var string
     */
    protected string $orderByColumnName = 'id';

    /**
     * @var bool
     */
    protected bool $paginate = true;

    /**
     * @var bool
     */
    protected bool $disablePaginationParameter = false;

    /**
     * Flag validation
     * @var bool
     */
    private bool $isValidated = false;

    /**
     * Validate pagination parameters
     *
     * @param \Illuminate\Http\Request $request
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateAndSetPaginationParametersYajra()
    {
        // Double validation will lead to bug
        if( $this->isValidated ) return $this;

        Validator::validate( \request()->all(), $this->getPaginationRulesYajra()->toArray() );

        $this->search = \request()->input( 'search.value' );
        $this->orderByDir = \request()->input( 'order.0.dir' , 'asc');
        $this->orderByColumnName = \request()->input( 'order.0.name' )?? $this->orderByColumnName;

        if( ! $this->disablePaginationParameter )
            $this->paginate = is_true( \request()->input( 'paginate' ) ?? $this->paginate );

        // fix bug so Yajra doesn't generate error since we handle ordering
        if( \request()->input( 'order.0.dir' ) || \request()->input( 'order.0.name' , 'asc') )
        {
            // NB: Once you call request->merge,
            //  request->input won't work anymore for nested values like order.0.name It will return null
            //  but will still work for direct values like order.
            // However, request->all() will still work.
            \request()->merge([
                'order' =>
                    [
                        "name" => $this->orderByColumnName,
                        "dir" =>  $this->orderByDir,
                        "column" => 0 // fix this
                    ],
                "columns" => [
                    [
                        "data" => \request()->input( 'columns.0.data' )?? $this->orderByColumnName,
                        "orderable" => true
                    ]
                ]
            ]);
        }

        $this->isValidated = true;
        return $this;
    }

    /**
     * @return Collection
     */
    private function getPaginationRulesYajra(): Collection
    {
        return collect([
            'paginate' => 'nullable',
            'search.value' => 'nullable|string|max:250',
            'order.0.dir' => [
                'nullable',
                'string',
                'max:10',
                Rule::in([ 'asc', 'desc' ])
            ],
            'order.0.name' => 'nullable|string|max:100',
        ]);
    }

    /**
     * @return Builder
     */
    abstract protected function getQuery(): Builder;

    /**
     * @param Builder $query
     * @return Builder|mixed
     */
    protected function filterQuery( Builder $query )
    {
        return $query->when($this->search, function (Builder $builder) {
            $builder->where("id",  $this->search );
        });
    }

    /**
     * @return Builder
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function searchableValidatedQuery()
    {
        return  $this->validateAndSetPaginationParametersYajra()->getQuery();
    }

    /**
     * @return Builder|mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function filteredSearchableValidatedQuery()
    {
        return $this->filterQuery( $this->searchableValidatedQuery() );
    }

    /**
     * https://packagist.org/packages/yajra/laravel-datatables
     *
     * @param Builder $data
     * @return array|Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    protected function paginateYajra(?IYajraEloquentResultProcessorInterface $processor = null)
    {
        if( ! $this->validateAndSetPaginationParametersYajra()->paginate )
            return $this->filteredSearchableValidatedQuery()->get();

        // Seems this is duplicate call
//        $this->searchableValidatedQuery();

        return YajraEloquentDataTableExtended::instance(
            $this->searchableValidatedQuery()
                ->orderByRaw($this->orderByColumnName . ' ' . $this->orderByDir )
              )
            ->filter( function ($query)  { $this->filterQuery( $query ); } )
            ->customMake( $processor );
    }
}
