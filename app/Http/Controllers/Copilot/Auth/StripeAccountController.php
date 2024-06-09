<?php

namespace App\Http\Controllers\Copilot\Auth;

use App\Http\Controllers\Controller;
use App\Http\Responses\OkResponse;
use App\ModelsExtended\Country;
use App\ModelsExtended\User;
use App\Repositories\Stripe\StripeConnectSDK;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Laravel\Lumen\Http\Redirector;
use Stripe\Exception\ApiErrorException;

class StripeAccountController extends Controller
{
    /**
     * @var StripeConnectSDK
     */
    private StripeConnectSDK $SDK;

    /**
     * @var User|Authenticatable|null
     */
    private $user;

    public function __construct()
    {
        $this->SDK = new StripeConnectSDK();
        $this->user = auth()->user();
    }

    /**
     * @return mixed
     * @throws ApiErrorException|\Exception
     */
    public function create()
    {
        if( $this->user->user_stripe_account && $this->user->user_stripe_account->connect_boarding_completed )
            throw new \Exception( 'You have an account linked!' );

        try {

            if( ! $this->user->user_stripe_account || ! $this->user->user_stripe_account->getStripeConnectIdAttribute() )
                $this->createUserStripeConnectAccount();

            return  $this->completeAccountCreation();

        }catch (\Exception $exception){
            Log::error( $exception->getMessage(), $exception->getTrace() );
            throw new \Exception( "Sorry, your account could not be created! Please, try again later", 0, $exception );
        }
    }

    /**
     * @return mixed
     * @throws ApiErrorException
     * @throws \Exception
     */
    public function completeAccountCreation()
    {
        if( ! $this->user->user_stripe_account
            || ! $this->user->user_stripe_account->getStripeConnectIdAttribute() )
            throw new \Exception( 'You do NOT have an account created yet!' );

        if( $this->user->user_stripe_account && $this->user->user_stripe_account->connect_boarding_completed )
            throw new \Exception( 'You already have an account linked successfully!' );

        try {

            $link = $this->SDK->createConnectOnboardingLink(
                $this->user->user_stripe_account->getStripeConnectIdAttribute(),
                '/copilot/auth/stripe/onboarded'
            );

            return new OkResponse( [ "url" => $link->url ] );

        }catch (\Exception $exception){
            Log::error( $exception->getMessage(), $exception->getTrace() );
            throw new \Exception( "Sorry, your on-boarding link could not be created! Please, try again later", 0, $exception );
        }
    }

    /**
     * @param Request $request
     * @return RedirectResponse|Redirector|OkResponse
     */
    public function onboarded(Request $request)
    {
        Artisan::call( 'payments:monitor-connect-account ' . $request->user_id );

        if( $request->wantsJson() )
            return new OkResponse(message("You can close this page!")) ;

        return redirect(copilotAppUrl());
//        return view('vanishing');
    }

    /**
     * @return void
     * @throws ApiErrorException
     */
    private function createUserStripeConnectAccount()
    {
//        // Using standard account because it is free
//        // https://stripe.com/en-fr/connect/pricing
//
//        $this->user->user_stripe_account()->updateOrInsert(
//            ['user_id' => $this->user->id],
//            [
//                'stripe_connect_account' => json_encode(
//                    $this->SDK->createConnectStandardAccount(
//                                    $this->user->first_name, $this->user->last_name,
//                                    $this->user->email,
//                                    $this->user->id, $this->user->phone
//                            )->toArray())
//            ]
//        );

        // Using Express account
        // https://stripe.com/en-fr/connect/pricing

        $this->user->user_stripe_account()->updateOrInsert(
            ['user_id' => $this->user->id],
            [
                'stripe_connect_account' => json_encode(
                    $this->SDK->createConnectExpressOrCustomAccount(
                        $this->user->first_name, $this->user->last_name,
                        $this->user->email,
                        $this->user->id, $this->user->phone,
                        "express",
                        $this->user->country->id === Country::US ? "full" : "recipient",
                        $this->user->country->iso_3166_1_alpha2_code
                    )->toArray())
            ]
        );

        $this->user->refresh();
    }

    /**
     * @return OkResponse|mixed
     * @throws ApiErrorException
     * @throws \Exception
     */
    public function expressDashboard()
    {
        if( ! $this->user->user_stripe_account
            || ! $this->user->user_stripe_account->getStripeConnectIdAttribute() )
            throw new \Exception( 'You do NOT have an account created yet!' );

        if( !$this->user->user_stripe_account->connect_boarding_completed )
            return $this->completeAccountCreation();

        try {

            $link = $this->SDK->createExpressAccountLoginURL(
                $this->user->user_stripe_account->getStripeConnectIdAttribute()
            );

            return new OkResponse( [ "url" => $link->url ] );

        }catch (\Exception $exception){
            Log::error( $exception->getMessage(), $exception->getTrace() );
            throw new \Exception( "Sorry, your on-boarding link could not be created! Please, try again later", 0, $exception );
        }
    }
}
