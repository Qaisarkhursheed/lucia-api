<?php

namespace App\ModelsExtended;

use App\Models\AdvisorRequestFeedbackRating;
use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;

/**
 * @property AdvisorRequest $advisor_request
 */
class AdvisorRequestFeedback extends \App\Models\AdvisorRequestFeedback implements IDeveloperPresentationInterface
{
    public function advisor_request()
    {
        return $this->belongsTo(AdvisorRequest::class);
    }

    /**
     * @return $this
     */
    public function updateRating(): AdvisorRequestFeedback
    {
        $this->average_rating = $this->advisor_request_feedback_ratings->count()?
            $this->advisor_request_feedback_ratings->average( fn( AdvisorRequestFeedbackRating $rating ) => $rating->rating )
            : 0;

        $this->updateQuietly();

        return $this;
    }

    public function presentForDev(): array
    {
       return [
           'created_at' => $this->created_at->toIso8601String(),
           'average_rating' => $this->average_rating,
           'ratings' => $this->advisor_request_feedback_ratings
               ->map(  fn( AdvisorRequestFeedbackRating $rating ) => [ $rating->feedback_topic->description => $rating->rating ] )
       ];
    }
}
