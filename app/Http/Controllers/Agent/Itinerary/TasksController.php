<?php

namespace App\Http\Controllers\Agent\Itinerary;

use App\Http\Middleware\ConvertStringsToBooleanMiddleware;
use App\ModelsExtended\ItineraryTask;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * @property ItineraryTask $model
 */
class TasksController extends ItineraryItemsController
{
    public function __construct()
    {
        $this->middleware( ConvertStringsToBooleanMiddleware::class );
        parent::__construct( "task_id" );
    }

    public function getDataQuery(): Builder
    {
        return  $this->limitBookingToItineraryOwner( ItineraryTask::query())
            ->where( "is_completed", false )
            ->where( "itinerary_id", $this->getItineraryId() );
    }

    public function getCommonRules()
    {
        return [
            'deadline' => 'filled|date_format:Y-m-d|after_or_equal:today',
            'title' => 'required|max:100',
            'notes' => 'nullable|max:1000',
            'is_completed' => 'required|boolean'
        ];
    }

    public function fetch()
    {
        return $this->model->presentForDev();
    }

    public function markCompleted()
    {
        $this->model->update(["is_completed" => true ]);
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

        $this->getItinerary()->itinerary_tasks()
            ->create( $this->validatedRules( $this->getCommonRules() ) );

        return $this->fetchAll();
    }

}
