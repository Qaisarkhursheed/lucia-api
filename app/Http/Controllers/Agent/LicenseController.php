<?php

namespace App\Http\Controllers\Agent;

use App\Console\Commands\Payments\DetectSubscriptions;
use App\Http\Controllers\Controller;
use App\Http\Middleware\Authenticate;
use App\Http\Responses\OkResponse;
use App\Models\StripeCheckoutLog;
use App\ModelsExtended\ApplicationProductPrice;
use App\ModelsExtended\StripeSubscriptionHistory;
use App\ModelsExtended\User;
use App\Repositories\Stripe\StripeSubscriptionSDK;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Stripe\Invoice;
use App\ModelsExtended\UserRole;

class LicenseController extends Controller
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


        // I can make this auto fix if it ever occurs.
        if (!optional($this->user->user_stripe_account)->getStripeCustomerIdAttribute())
            throw new \Exception("You do not have an operable account yet. Please, contact the customer service.");
    }

//    /**
//     * @param Request $request
//     * @return OkResponse
//     * @throws ValidationException
//     * @throws \Exception
//     */
//    public function checkout(Request $request): OkResponse
//    {
//        $this->validatedRules([
//            'subscription_price_id' => 'required|numeric|exists:application_product_prices,id',
//            'redirect_url' => 'required|url'
//        ]);
//
//        // make this atomic
//        // ----------------------------------------------------
//        return $this->runInALock('purchasing-subscription-' . $this->user->id,
//            function () use ($request) {
//
//                // make sure this does not have a subscription active
//                if ($this->user->refresh()->has_valid_license)
//                    throw new \Exception("You already have an active subscription.");
//
//                if (DetectSubscriptions::detectIfCustomerAlreadyHasActiveSubscription($this->user))
//                    throw new \Exception('You already have an active subscription. Please, refresh and it should appear on your account!');
//
//                $price = ApplicationProductPrice::findOne($request->input('subscription_price_id'));
//
//                try {
//
//                    $session = $this->SDK->createCheckout(
//                        $price->stripe_price_id,
//                        $this->user->user_stripe_account->getStripeCustomerIdAttribute(),
//                        $request->input('redirect_url'),
//                        $this->getAuthToken($request),
//                        '/agent/license/checkout-successful',
//                        env('STRIPE_LUCIA_APPLIED_TAX_RATES') ?
//                            explode(",", env('STRIPE_LUCIA_APPLIED_TAX_RATES'))
//                            : [],
//                        ! $this->user->stripe_subscription_histories->count() && intval( env('STRIPE_LUCIA_TRIAL_DAYS') ) ?
//                            intval( env('STRIPE_LUCIA_TRIAL_DAYS') ) : null
//                    );
//
//                    $this->user->stripe_checkout_logs()->create([
//                        'redirect_url' => $request->input('redirect_url'),
//                        'session_id' => $session->id,
//                        'stripe_response' => json_encode($session->toArray()),
//                    ]);
//
//                    return new OkResponse(["url" => $session->url]);
//                } catch (\Exception $exception) {
//                    Log::error($exception->getMessage(), $exception->getTrace());
//                    throw new \Exception("Sorry, your checkout could not be created! Please, try again later", 0, $exception);
//                }
//            });
//    }

    /**
     * @param string $session_id
     * @return Model|HasMany|StripeCheckoutLog
     */
    private function getStripeCheckoutLog(string $session_id)
    {
        return $this->user->stripe_checkout_logs()
            ->where('session_id', $session_id)
            ->firstOrFail();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws ValidationException
     * @throws \Exception
     */
    public function checkoutSuccessful(Request $request): RedirectResponse
    {
        $this->validatedRules([
            'session_id' => 'required|string'
        ]);

        // make this atomic
        // ----------------------------------------------------
        return $this->runInALock('purchasing-subscription-' . $this->user->id,
            function () use ($request) {

                // make sure this does not have a subscription active
                if ($this->user->refresh()->has_valid_license)
                    throw new \Exception("You already have an active subscription.");

                try {

                    $stripe_checkout_log = $this->getStripeCheckoutLog($request->input('session_id'));

                    $session = $this->SDK->retrieveCheckout($request->input('session_id'));

                    $stripe_checkout_log->update([
                        'stripe_response' => json_encode($session->toArray())
                    ]);

                    if ($session->status == 'complete' || $session->payment_status == 'paid')
                        DetectSubscriptions::addActiveSubscriptionToUser($this->user, $this->SDK->fetchSubscription($session->subscription));

                    return redirect()->to($stripe_checkout_log->redirect_url);
                } catch (\Exception $exception) {
                    Log::error($exception->getMessage(), $exception->getTrace());
                    throw new \Exception("Sorry, your checkout could not be created! Please, try again later", 0, $exception);
                }
            });
    }

    /**
     * @param Request $request
     * @return OkResponse
     * @throws ValidationException
     * @throws \Exception
     */
    public function billingPortal(Request $request): OkResponse
    {
        $this->validatedRules([
            'redirect_url' => 'required|url'
        ]);

        $userRole = Authenticate::getUserRole();

        // make sure this does not have a subscription active
        if ($userRole->stripe_subscription)
            throw new \Exception("You do not have any subscription.");

        try {

            $session = $this->SDK->createBillingPortal(
                $this->user->user_stripe_account->getStripeCustomerIdAttribute(),
                $request->input('redirect_url')
            );

            return new OkResponse(["url" => $session->url]);

        } catch (\Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());
            throw new \Exception("Sorry, your billing portal could not be created! Please, try again later", 0, $exception);
        }
    }

    /**
     * @param Request $request
     * @return OkResponse
     * @throws ValidationException
     * @throws \Exception
     */
    public function subscribe(Request $request): OkResponse
    {
        $this->validatedRules([
            'subscription_price_id' => 'required|numeric|exists:application_product_prices,id',
            'default_payment_method' => 'filled|string',
        ]);

        return $this->subscribeCore($request,
            $request->input('default_payment_method', $this->user->user_stripe_account->getStripeDefaultSourceAttribute())
        );
    }

    /**
     * @param Request $request
     * @return OkResponse
     * @throws ValidationException
     * @throws \Exception
     */
    public function subscribeWithCard(Request $request, PaymentMethodsController $paymentMethodsController): OkResponse
    {
        $this->validatedRules([
            'subscription_price_id' => 'required|numeric|exists:application_product_prices,id',

            'number' => 'required|numeric',
            'exp_month' => 'required|numeric',
            'exp_year' => 'required|numeric',
            'cvc' => 'filled|numeric',
        ]);


        $card_token = $paymentMethodsController->generateCardToken($request)->getData()->card_token;
        $stripe_token_id = $paymentMethodsController->addCardTokenPaymentMethod($card_token)->getData()->stripe_token_id;
        return $this->subscribeCore($request, $stripe_token_id);
    }

    /**
     * @param Request $request
     * @param string $stripe_token_id
     * @return mixed
     * @throws \Exception
     */
    private function subscribeCore(Request $request, string $stripe_token_id)
    {
        // make this atomic
        // ----------------------------------------------------
        return $this->runInALock('purchasing-subscription-' . $this->user->id,
            function () use ($request, $stripe_token_id) {

                $userRole = Authenticate::getUserRole();
                // make sure this does not have a subscription active
                if ( $userRole->has_valid_license)
                    throw new \Exception("You already have an active subscription.");

                $price = ApplicationProductPrice::getById($request->input('subscription_price_id'));

                try {

                    DetectSubscriptions::addActiveSubscriptionToUser($this->user, $this->SDK->createSubscription(
                        $price->stripe_price_id,
                        $this->user->user_stripe_account->getStripeCustomerIdAttribute(),
                        $stripe_token_id,
                        null,
                        [
                            "role_id" => $userRole->role_id
                        ]
                    ));

                    return new OkResponse(message("Subscribed Successfully!"));
                } catch (\Exception $exception) {
                    Log::error($exception->getMessage(), $exception->getTrace());
                    throw new \Exception("Sorry, your subscription could not be created! Make sure you have a valid payment method.", 0, $exception);
                }

            });
    }

    /**
     * @return OkResponse
     * @throws \Exception
     */
    public function fetchAllInvoices()
    {
        try {

            $invoices = $this->SDK->listAllInvoices(
                $this->user->user_stripe_account->getStripeCustomerIdAttribute()
            );

            $invoices = collect($invoices)->map(function (Invoice $invoice) {
                return StripeSubscriptionSDK::formatInvoiceForDisplay($invoice);
            });

            return new OkResponse($invoices);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());
            throw new \Exception("Sorry, your invoice listing failed!", 0, $exception);
        }
    }

    /**
     * @return OkResponse
     * @throws \Exception
     */
    public function fetchPendingInvoices()
    {
        try {

            $invoices = $this->SDK->listPendingInvoices(
                $this->user->user_stripe_account->getStripeCustomerIdAttribute()
            );
            $invoices = array_merge($invoices, $this->SDK->listUncollectibleInvoices(
                $this->user->user_stripe_account->getStripeCustomerIdAttribute()
            ));

            $invoices = collect($invoices)->map(function (Invoice $invoice) {
                return StripeSubscriptionSDK::formatInvoiceForDisplay($invoice);
            });

            return new OkResponse($invoices);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());
            throw new \Exception("Sorry, your invoice listing failed!", 0, $exception);
        }
    }

    /**
     * @param Request $request
     * @return OkResponse
     * @throws ValidationException
     */
    public function payPendingInvoice(Request $request): OkResponse
    {
        $this->validatedRules([
            'stripe_invoice_id' => 'required|string',
        ]);

        try {

            $invoice = StripeSubscriptionSDK::formatInvoiceForDisplay(
                $this->SDK->payInvoice(
                    $request->input('stripe_invoice_id')
                )
            );

            // call artisan here
            Artisan::call('payments:monitor-subscriptions ' . auth()->id());

            return new OkResponse($invoice);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());
            throw new \Exception("Sorry, your payment could not be done!", 0, $exception);
        }
    }

    /**
     * Works as fetch also but will first try to fetch from stripe
     * Also good in case webhook fails, user can manually invoke it
     *
     * @return OkResponse
     * @throws \Exception
     */
    public function refreshSubscription(): OkResponse
    {
        try {
            // call artisan here
            Artisan::call('payments:monitor-subscriptions ' . auth()->id());

            return new OkResponse(
                auth()->user()->refresh()->last_subscription->formatForDisplay()
            );
        } catch (\Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());
            throw new \Exception("Sorry, your details could not be fetched!", 0, $exception);
        }
    }

    /**
     * @return OkResponse
     * @throws \Exception
     */
    public function cancelSubscription(): OkResponse
    {
        // make this atomic
        // ----------------------------------------------------
        return $this->runInALock('cancelling-subscription-' . $this->user->id,
            function () {

                $userRole = Authenticate::getUserRole();

                // Note that you can have a license and not subscription
                // because your subscription may still be waiting to cancel out at the end of the period
                if (!$userRole->has_valid_license
                    || !$userRole->hasActiveStripeSubscriptionAttribute()
                )
                    throw new \Exception("You don't have an active subscription.");

                try {

                    $userRole->stripe_subscription = $this->SDK->cancelSubscription($userRole->getStripeSubscriptionIdAttribute());
                    $userRole->updateQuietly();
                    StripeSubscriptionHistory::pushToHistory($userRole);

                    return new OkResponse(message("Subscription cancelled!"));
                } catch (\Exception $exception) {
                    Log::error($exception->getMessage(), $exception->getTrace());
                    throw new \Exception("Sorry, your subscription cancellation could not be done!", 0, $exception);
                }
            });
    }
    public function renewSubscription(): OkResponse
    {
                $userRole = Authenticate::getUserRole();
                // Note that you can have a license and not subscription
                // because your subscription may still be waiting to cancel out at the end of the period
                if (!$userRole->has_valid_license
                    || !$userRole->hasActiveStripeSubscriptionAttribute()
                )
                    throw new \Exception("You don't have an active subscription.");
                try {

                    $userRole->stripe_subscription = $this->SDK->updateSubscription($userRole->getStripeSubscriptionIdAttribute());
                    $userRole->updateQuietly();
                    StripeSubscriptionHistory::pushToHistory($userRole);

                    return new OkResponse(message("Subscription updated!"));
                } catch (\Exception $exception) {
                    Log::error($exception->getMessage(), $exception->getTrace());
                    throw new \Exception("Sorry, your subscription renewal could not be done!", 0, $exception);
                }
    }
    public function newSubscription(Request $request): OkResponse
    {
        $userRole = $userRole = Authenticate::getUserRole();


        $price = ApplicationProductPrice::getById($request->input('subscription_price_id'));
        $this->createSubscriptionWithTrialIfAvailable( $userRole, $price, $request );
        return new OkResponse( message( "Your subscription is created successfully!" ) );
    }
    public function createSubscriptionWithTrialIfAvailable(UserRole $userRole, ApplicationProductPrice $price, $request)
    {
        // First do monitor just to be sure
        // if ($userRole->has_valid_license) return;

        // call artisan here
        Artisan::call('payments:detect-subscriptions ' . $userRole->user_id);
        $userRole->refresh();

        // if trial days is entered on the environment
        $STRIPE_LUCIA_TRIAL_DAYS = intval(env('STRIPE_LUCIA_TRIAL_DAYS'));

        // disable trial for yearly
        if( $price->id === ApplicationProductPrice::LUCIA_EXPERIENCE_YEARLY )
            $STRIPE_LUCIA_TRIAL_DAYS = null;


        $stripe_price_id = $price->stripe_price_id;

        //If partner agent then take the partner payment plan
        if($userRole->user->preferred_partner_id):
            $stripe_price_id = ($price->id === ApplicationProductPrice::LUCIA_EXPERIENCE_YEARLY)?$userRole->user->partner->annual_price:$userRole->user->partner->monthly_price;
        endif;

        // only applicable if you have never had a license or subscription before on the platform
        // add payment method if specified
        if( $request->get("stripe_payment_token") )
        (new StripeSubscriptionSDK())->createPaymentSourceForCustomer(
               $this->user->refresh()->user_stripe_account->getStripeCustomerIdAttribute(),
               $request->get("stripe_payment_token")
        );
        $SDK = new StripeSubscriptionSDK();
        // if (!$userRole->has_valid_license && !$userRole->user->stripe_subscription_histories()->exists()) {
            DetectSubscriptions::addActiveSubscriptionToUser($userRole->user,
                $SDK->createSubscription(
                    $stripe_price_id,
                    $userRole->user->user_stripe_account->getStripeCustomerIdAttribute(), null,
                    $STRIPE_LUCIA_TRIAL_DAYS,
                    [
                        "role_id" => $userRole->role_id
                    ]
                )
            );
        // }
    }


}

