<?php

namespace App\Http\Controllers\Admin\Copilots;

use App\Http\Controllers\Enhancers\CRUDEnabledController;
use App\Http\Controllers\Enhancers\YajraPaginableTraitController;
use App\Http\Middleware\ConvertStringsToBooleanMiddleware;
use App\ModelsExtended\AdvisorRequestType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * @property AdvisorRequestType $model
 */
class AdvisorTasksController  extends CRUDEnabledController
{
    use YajraPaginableTraitController;

    public function __construct()
    {
        $this->middleware( ConvertStringsToBooleanMiddleware::class );
        parent::__construct( 'advisor_task_id' );
    }

    /**
     * @return array|Builder[]|\Illuminate\Database\Eloquent\Collection|JsonResponse
     * @throws ValidationException
     */
    public function fetchAll()
    {
        return $this->paginateYajra(  );
    }

    
    /**
     * @return Builder
     */
    protected function getQuery(): Builder
    {
        return AdvisorRequestType::query();
    }

    /**
     * @param Builder $query
     * @return Builder|mixed
     */
    protected function filterQuery(Builder $query)
    {
        return $query->when($this->search, function (Builder $builder) {
            $search = $this->search;
            $builder->where( function (Builder $builder) use ( $search ) {
                $builder->where("description", 'like', "%$search%")
                    ->orWhere("explanation", 'like', "%$search%");
            });
        });
    }

    /**
     * @inheritDoc
     */
    public function getDataQuery(): Builder
    {
        return $this->getQuery();
    }

    /**
     * @return array|void
     */
    public function getCommonRules()
    {
        return [
            'amount' => 'required|numeric',
            'description' => 'required|string|max:100',
            'copilot_remarks' => 'filled|string|max:1000',
            'explanation' => 'nullable|string|max:800',
            'is_active' => 'required|boolean',
        ];
    }

    /**
     * @inheritDoc
     */
    public function store(Request $request)
    {
        $this->model = AdvisorRequestType::create( $this->validatedRules( $this->getCommonRules() ) );
        return $this->fetch();
    }
}
