<?php

namespace App\Http\Controllers\Enhancers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;

trait PaginableTraitController
{
    /**
     * @var int
     */
    protected $per_page = 10;

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * Validate pagination parameters
     *
     * @param \Illuminate\Http\Request $request
     * @throws \Illuminate\Validation\ValidationException
     */
    private function validateAndSetPaginationParameters()
    {
        Validator::validate( \request()->all(), $this->getPaginationRules()->toArray() );
        $this->per_page = \request( 'per_page', $this->per_page );
        $this->page = \request( 'page', $this->page );
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    protected function getPaginationRules()
    {
        return collect([
            'per_page' => 'nullable|int|max:100',
            'page' => 'nullable|int|max:10000',
        ]);
    }

    /**
     * @param Builder $data
     * @return LengthAwarePaginator
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function paginate( $data )
    {
        // Alternate
        // https://packagist.org/packages/yajra/laravel-datatables

        $this->validateAndSetPaginationParameters( );
        return $data->paginate( $this->per_page, ['*'], 'page', $this->page );

        // If you want a cursor-nated version instead of numbers
        // "next_page_url": "http://192.168.1.38:14080/admin/agents/list?cursor=eyJ1c2Vycy5pZCI6bnVsbCwiX3BvaW50c1RvTmV4dEl0ZW1zIjp0cnVlfQ"
        // return $data->cursorPaginate( $this->per_page );
    }
}
