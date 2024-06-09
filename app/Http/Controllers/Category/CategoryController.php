<?php
namespace App\Http\Controllers\Category;

use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Nette\NotImplementedException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController  extends Controller {

   
    public function showAll(Request $request)
    {
        return Category::orderBy("id")->select('id','name', 'category_type')->get();
    }

    public function store(Request $request)
    {
       
    }

    /**
     * @inheritDoc
     */
    public function getDataQuery(): Builder
    {
        return $this->getQuery();
    }

    /**
     * @inheritDoc
     */
    public function processYajraEloquentResult($result): array
    {
        return $result->map->presentForDev()
            ->all();
    }
}
