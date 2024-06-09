<?php

namespace App\Http\Controllers\Copilot;

use App\ModelsExtended\AdvisorRequest;
use App\ModelsExtended\AdvisorRequestStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RequestMailResponseController extends \App\Http\Controllers\Agent\ProfileController
{

    private AdvisorRequest $model;

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Laravel\Lumen\Http\Redirector
     * @throws \Illuminate\Validation\ValidationException
     */
    public function declineRequest(Request $request)
    {
        $this->validatedRules( [
            "token" => "required|min:10",
            "id" => "required|exists:advisor_request,id"
        ]);

        $this->model = AdvisorRequest::getById( $request->input("id") );

        if(
            self::isValidRequest( $this->model, $request->input("token") )
            &&
            (
                $this->model->advisor_request_status_id === AdvisorRequestStatus::ACCEPTED
                ||  $this->model->advisor_request_status_id === AdvisorRequestStatus::PAID
            )
        ) self::pushRequestBackToPool( $this->model );

        return redirect(copilotAppUrl());
    }

    /**
     * @param AdvisorRequest $request
     * @return AdvisorRequest
     */
    public static function pushRequestBackToPool(AdvisorRequest $request): AdvisorRequest
    {
        if(
            (
                $request->advisor_request_status_id === AdvisorRequestStatus::ACCEPTED
                ||  $request->advisor_request_status_id === AdvisorRequestStatus::PAID
            ) && $request->advisor_assigned_copilot
        )
        {
            $request->update(["advisor_request_status_id" => AdvisorRequestStatus::PAID]);// before was refund,to give refund to the advisor after cancelling
            $request->advisor_assigned_copilot()->delete();
        }
        return $request;
    }

    /**
     * @param AdvisorRequest $advisorRequest
     * @param string $hashKey
     * @return bool
     */
    public static function isValidRequest( AdvisorRequest $advisorRequest, string $hashKey): bool
    {
        if( !$advisorRequest->advisor_assigned_copilot ) return false;
       return Hash::check( self::tagUrl($advisorRequest), $hashKey );
    }

    /**
     * @param AdvisorRequest $advisorRequest
     * @return string
     */
    public static function tagUrl(AdvisorRequest $advisorRequest): string
    {
        return sprintf("%s-%s-%s-%s",
            $advisorRequest->id,
            $advisorRequest->request_title,
            $advisorRequest->advisor_assigned_copilot->user->id,
            $advisorRequest->advisor_assigned_copilot->created_at->timestamp
        );
    }

    /**
     * @param AdvisorRequest $advisorRequest
     * @return string
     */
    public static function tagUrlEncrypted(AdvisorRequest $advisorRequest): string
    {
        return Hash::make( self::tagUrl($advisorRequest) );
    }
}
