<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Http\Responses\OkResponse;
use App\ModelsExtended\ApplicationProductPrice;
use App\ModelsExtended\StripeSubscriptionHistory;
use App\ModelsExtended\User;
use App\Repositories\Stripe\StripeSubscriptionSDK;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Stripe\Card;

class PaymentMethodsController extends Controller
{
    /**
     * @var StripeSubscriptionSDK
     */
    private StripeSubscriptionSDK $SDK;

    /**
     * @var Authenticatable|User
     */
    private $user;

    public function __construct(StripeSubscriptionSDK $SDK)
    {
        $this->SDK = $SDK;
        $this->user = auth()->user();

//        // I can make this auto fix if it ever occurs.
//        if( ! optional( $this->user->user_stripe_account )->getStripeCustomerIdAttribute() )
//            throw new \Exception( "You do not have an operable account yet. Please, contact the customer service." );

    }

    /**
     * @param Request $request
     * @return OkResponse
     * @throws ValidationException
     * @throws \Exception
     */
    public function addPaymentMethod(Request $request): OkResponse
    {
        $this->validatedRules([
            'payment_token' =>  'required|string',
        ]);

        try {

            $source =  $this->SDK->createPaymentSourceForCustomer(
                $this->user->user_stripe_account->getStripeCustomerIdAttribute(),
                $request->input( 'payment_token'  )
            );

            return new OkResponse( StripeSubscriptionSDK::formatSourceForDisplay( $source ) );
        }catch (\Exception $exception){
            Log::error( $exception->getMessage(), $exception->getTrace() );
            throw new \Exception( "Sorry, your payment source could not be created!", 0, $exception );
        }
    }

    /**
     * @param string $card_token
     * @return OkResponse
     * @throws \Exception
     */
    public function addCardTokenPaymentMethod(string $card_token): OkResponse
    {
        try {

            $card =  $this->SDK->createCard(
                $this->user->user_stripe_account->getStripeCustomerIdAttribute(),
                $card_token
            );

            return new OkResponse( StripeSubscriptionSDK::formatCardForDisplay( $card ) );
        }catch (\Exception $exception){
            Log::error( $exception->getMessage(), $exception->getTrace() );
            throw new \Exception( "Sorry, your card could not be created!", 0, $exception );
        }
    }

//    /**
//     * @param Request $request
//     * @return OkResponse
//     * @throws ValidationException
//     * @throws \Exception
//     */
//    public function changeDefaultPaymentMethod(Request $request): OkResponse
//    {
//        $this->validatedRules([
//            'stripe_token_id' =>  'nullable|string',
//        ]);
//
//        try {
//
//            if( $request->has('stripe_token_id') )
//            {
//                $stripe_customer =
//                    $this->user->user_stripe_account->updateDefaultPaymentMethod(
//                        $request->input( 'stripe_token_id'  )
//                    );
//
//            }else{
//
//                $stripe_customer = $this->SDK->getCustomer(
//                    $this->user->user_stripe_account->getStripeCustomerIdAttribute()
//                );
//
//                if( ! $stripe_customer->default_source )
//                    throw new \Exception("No default payment method set!");
//            }
//
//            $p = $this->SDK->getCustomerSource( $stripe_customer->id, $stripe_customer->default_source );
//
//            return new OkResponse(  StripeSubscriptionSDK::formatSourceForDisplay( $p ) );
//        }catch (\Exception $exception){
//            Log::error( $exception->getMessage(), $exception->getTrace() );
//            throw new \Exception($exception->getMessage(), 0, $exception);
//        }
//    }


    /**
     * @param Request $request
     * @return OkResponse
     * @throws ValidationException
     * @throws \Exception
     */
    public function generateCardToken(Request $request): OkResponse
    {
        $this->validatedRules([
            'number' => 'required|numeric',
            'exp_month' => 'required|numeric',
            'exp_year' => 'required|numeric',
            'cvc' => 'filled|numeric',
        ]);

        try {

            $token = $this->SDK->createCardToken(
                $request->input('number'), $request->input('exp_month'),
                $request->input('exp_year'), $request->input('cvc'),
            );

            return new OkResponse(['payment_token' => $token->id]);

        } catch (\Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());
            throw new \Exception("Sorry, your token could not be created! Please, make sure you entered the right details.", 0, $exception);
        }
    }

    /**
     * @return OkResponse
     * @throws \Exception
     */
    public function deletePaymentMethod(): OkResponse
    {
        $this->validatedRules([
            'stripe_token_id' => 'required|string',
        ]);

        try {
            $this->SDK->deleteSource(
                $this->user->user_stripe_account->getStripeCustomerIdAttribute(),
                \request('stripe_token_id')
            );

            return new OkResponse(message("Payment method Deleted!"));
        } catch (\Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());
            throw new \Exception("Sorry, your payment method deletion could not be done!", 0, $exception);
        }
    }

    /**
     * @return OkResponse
     * @throws \Exception
     */
    public function listAll(): OkResponse
    {
        try {

            return new OkResponse(
                $this->SDK->listAllCardPaymentMethods(
                    $this->user->user_stripe_account->getStripeCustomerIdAttribute()
                )
            );

            // This is currently not listing all payment methods that really
            // exists on the account
//            return new OkResponse($this->SDK->getCustomerSources(
//                $this->user->user_stripe_account->getStripeCustomerIdAttribute()
//            )
//                ->map(fn($source) => StripeSubscriptionSDK::formatSourceForDisplay( $source ) )
//                ->toArray()
//            );
        } catch (\Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());
            throw new \Exception("Sorry, your card listing failed!", 0, $exception);
        }
    }
}
