<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Enhancers\CRUDEnabledController;
use App\Http\Controllers\Enhancers\YajraPaginableTraitController;
use App\Http\Responses\OkResponse;
use App\Mail\Auth\RegistrationAccessCodeInvitationMail;
use App\ModelsExtended\RegistrationAccessCode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/**
 * @property RegistrationAccessCode $model
 */
class RegistrationAccessCodesController extends CRUDEnabledController
{
    use YajraPaginableTraitController;

    public function __construct()
    {
        parent::__construct( "access_code_id");
    }

    public function fetchAll()
    {
        return $this->paginateYajra( );
    }

    /**
     * @inheritDoc
     */
    protected function getQuery(): Builder
    {
        return RegistrationAccessCode::query();
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function store(Request $request)
    {
        return RegistrationAccessCode::generateCode();
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function mail(Request $request)
    {
        $this->validatedRules([
           "emails" => 'required|array|min:1|max:100',
           "emails.*" => 'required|email|unique:users,email',
        ]);

        foreach ( array_unique($request->input('emails')) as  $email )
        {
            // send email
            Mail::send( new RegistrationAccessCodeInvitationMail( $this->model->code, $email  ) );
        }

        return new OkResponse();
    }

    /**
     * @inheritDoc
     */
    public function getDataQuery(): Builder
    {
        return $this->getQuery();
    }

}
