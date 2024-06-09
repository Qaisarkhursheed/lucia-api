<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Enhancers\CRUDEnabledController;
use App\Http\Controllers\Enhancers\YajraPaginableTraitController;
use App\Http\Responses\ExpectionFailedResponse;
use App\Http\Responses\OkResponse;
use App\ModelsExtended\UserNote;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * @property UserNote $model
 */
class NotesController extends CRUDEnabledController
{
    use YajraPaginableTraitController;

    public function __construct()
    {
        parent::__construct( "note_id");
    }

    public function getCommonRules()
    {
        return [
            'title' => 'required|string|max:150',
            'notes' => 'nullable|string|max:65535',
        ];
    }

    public function fetchAll()
    {
        return $this->paginateYajra( );
    }

    public function fetch()
    {
        return new OkResponse( $this->model->presentForDev() );
    }

    /**
     * @inheritDoc
     */
    protected function getQuery(): Builder
    {
        return UserNote::query()
            ->where( "user_notes.created_by_id" , auth()->id()  )
            ->select(
                'user_notes.id',
                'user_notes.created_at',
                'user_notes.updated_at',
                'user_notes.title',
                'user_notes.notes',
            );
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function store(Request $request)
    {
        $this->model = UserNote::create(
            array_merge([ 'created_by_id' => auth()->id() ],  $this->validatedRules( $this->getCommonRules() ) )
        );
        return $this->fetch();
    }

    /**
     * @inheritDoc
     */
    public function getDataQuery(): Builder
    {
        return $this->getQuery();
    }

    /**
     * @param Request $request
     * @return ExpectionFailedResponse|array
     * @throws \Illuminate\Validation\ValidationException
     */
    public function lookUp(Request $request)
    {
        $this->validatedRules(Arr::only( $this->getCommonRules(), [ 'title' ] ));
        $this->model = UserNote::getSavedNote( $request->input( 'title' ), auth()->id() );
        if( ! $this->model ) return new ExpectionFailedResponse( message( "Note was not found!" ) );

        return $this->model->presentForDev();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Support\Collection
     * @throws \Illuminate\Validation\ValidationException
     */
    public function autoComplete(Request $request)
    {
        $this->validatedRules(Arr::only( $this->getCommonRules(), [ 'title' ] ));

        return UserNote::query()
            ->where("title", 'like', sprintf("%%%s%%", $request->input( 'title' ) ) )
            ->where("created_by_id", auth()->id())
            ->limit(10)
            ->pluck("title");
    }
}
