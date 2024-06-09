<?php

namespace App\Http\Controllers\Admin\Copilots;

use App\Http\Controllers\Enhancers\CRUDEnabledController;
use App\Http\Controllers\Enhancers\YajraPaginableTraitController;
use App\Http\Middleware\ConvertStringsToBooleanMiddleware;
use App\ModelsExtended\RequestAvailableDiscount;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

/**
 * @property RequestAvailableDiscount $model
 */
class AdvisorDiscountsController  extends CRUDEnabledController
{
    use YajraPaginableTraitController;

    public function __construct()
    {
        $this->middleware( ConvertStringsToBooleanMiddleware::class );
        parent::__construct( 'advisor_discount_id' );
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
        return RequestAvailableDiscount::query();
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
                $builder->where("description", 'like', "%$search%");
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
            'is_active' => 'required|boolean',
            'limit_purchase_amount' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'limit_to_usage_count' => 'required|int|min:1|max:10000',
            'description' => 'required|string|max:50|min:3',
        ];
    }

    public function update(Request $request)
    {
        $this->model->update(  $this->validatedRules(Arr::except($this->getCommonRules(), ["description"])) );
        return $this->fetch();
    }

    /**
     * @inheritDoc
     */
    public function store(Request $request)
    {
        $this->model = RequestAvailableDiscount::create( $this->validatedRules( $this->getCommonRules() ) );
        return $this->fetch();
    }
}
