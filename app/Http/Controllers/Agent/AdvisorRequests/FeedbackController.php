<?php

namespace App\Http\Controllers\Agent\AdvisorRequests;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Enhancers\HasRouteModelControllerTrait;
use App\Http\Responses\OkResponse;
use App\ModelsExtended\AdvisorRequest;
use App\ModelsExtended\AdvisorRequestFeedback;
use App\ModelsExtended\AdvisorRequestStatus;
use App\ModelsExtended\FeedbackTopic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * @property AdvisorRequest $model
 */
class FeedbackController extends Controller
{
    use HasRouteModelControllerTrait;

    /**
     * @throws \App\Exceptions\RecordNotFoundException
     */
    public function __construct()
    {
        $this->invokeLoadRouteModelFunction ( "advisor_id", "advisor_request.id" );
    }

    /**
     * @return FeedbackController
     * @throws \Exception
     */
    protected function canSubmitFeedback(): FeedbackController
    {
        if( $this->model->advisor_request_feedback )
            throw new \Exception( 'You can not submit feedback again for this request because it has already been submitted!' );

        return $this;
    }

    /**
     * @param Request $request
     * @return OkResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function submitFeedback(Request $request)
    {
        $this->validatedRules([
            'efficiency' => 'required|min:1|max:5|numeric',
            'accuracy' => 'required|min:1|max:5|numeric',
            'completion' => 'required|min:1|max:5|numeric',
            'friendliness' => 'required|min:1|max:5|numeric',
        ]);

        $feedback = $this->canSubmitFeedback()->createFeedbackCore();
        $feedback->advisor_request_feedback_ratings()->createMany([
            [
                'rating' => $request->input('efficiency'),
                'feedback_topic_id' => FeedbackTopic::Efficiency
            ],
            [
                'rating' => $request->input('accuracy'),
                'feedback_topic_id' => FeedbackTopic::Accuracy
            ],
            [
                'rating' => $request->input('completion'),
                'feedback_topic_id' => FeedbackTopic::Task_Completion
            ],
            [
                'rating' => $request->input('friendliness'),
                'feedback_topic_id' => FeedbackTopic::Friendliness
            ],
        ]);

        $feedback->updateRating();

        return new OkResponse( $feedback->presentForDev() );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|AdvisorRequestFeedback
     */
    private function createFeedbackCore( )
    {
        return $this->model->advisor_request_feedback()->create([
            'average_rating' => 0,
        ]);
    }

    /**
     * @return Builder
     */
    public function getDataQuery(): Builder
    {
        return AdvisorRequest::with("advisor_request_status", "advisor_request_attachments")
            ->whereIn("advisor_request_status_id", [
                AdvisorRequestStatus::COMPLETED,
            ])
            ->orderByDesc("created_at");
    }
}

