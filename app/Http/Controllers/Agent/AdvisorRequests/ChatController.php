<?php

namespace App\Http\Controllers\Agent\AdvisorRequests;

use App\ModelsExtended\AdvisorRequest;
use App\ModelsExtended\AdvisorRequestStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ChatController extends \App\Http\Controllers\Copilot\MyRequests\ChatController
{

    /**
     * @param Request $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function extendDeadline(Request $request)
    {
        $this->checkCanChat()
            ->validatedRules([
            'deadline_time' => 'required|date_format:h\:i\ A',
            'deadline_day' => 'required|date_format:Y-m-d|after_or_equal:today',
        ]);

        $deadline_locale = Carbon::createFromFormat( 'Y-m-d', $request->input('deadline_day' )  );
        $deadline_time = Carbon::createFromTimeString( $request->input('deadline_time' ) );
        $deadline_locale =  $deadline_locale->clone()->setTimeFrom( $deadline_time );
        $deadline =  $deadline_locale->fromPreferredTimezoneToAppTimezone();

        if( $this->model->deadline_locale && $this->model->deadline_locale->greaterThan( $deadline_locale ) )
            throw new \Exception("You can only extend the deadline. You can not set it behind the current deadline!");


        $this->model->update([
            "deadline" => $deadline
        ]);

        return $this->fetch();
    }


    /**
     * @return Builder
     */
    public function getDataQuery(): Builder
    {
        return AdvisorRequest::with("advisor_request_status", "advisor_request_attachments")
            ->where('created_by_id', $this->user->id )
            ->whereIn("advisor_request_status_id", [
                AdvisorRequestStatus::ACCEPTED,
                AdvisorRequestStatus::COMPLETED,
                AdvisorRequestStatus::REFUNDED,
            ])
            ->orderByDesc("created_at");
    }
}

