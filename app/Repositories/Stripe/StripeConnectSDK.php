<?php

namespace App\Repositories\Stripe;

use App\ModelsExtended\StripeAuditLog;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Stripe\Account;
use Stripe\AccountLink;
use Stripe\Exception\ApiErrorException;
use Stripe\LoginLink;
use Stripe\PaymentIntent;
use Stripe\Transfer;

class StripeConnectSDK extends StripeSubscriptionSDK
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param string $return_url
     * @return string
     */
    private static function buildCallBackURL(string $return_url): string
    {
        return Str::of( env( 'APP_URL' ) )->rtrim( "/" )
            . Str::of($return_url . '?user_id=' . auth()->id() )->start( "/");
    }

    /**
     *  Use to create onboarding link for connect users
     *
     * @param string $account_id
     * @param string $return_url
     * @return AccountLink
     * @throws ApiErrorException
     */
    public function createConnectOnboardingLink(string $account_id, string $return_url = '/users/auth/stripe/onboarded' ): AccountLink
    {
        try {
            // let user call this if they want to onboard
            // https://stripe.com/docs/api/account_links/create

            // Links expires quickly
            return $this->stripe->accountLinks->create(
                [
                    'account' => $account_id,
                    'refresh_url' => self::buildCallBackURL($return_url),
                    'return_url' => self::buildCallBackURL($return_url),
                    'type' => 'account_onboarding',
                ]
            );
        } catch (ApiErrorException $exception) {
            StripeAuditLog::auditRaw($exception, __FUNCTION__, self::mergeArrayWithRequestInputs(func_get_args()));
            throw  $exception;
        }
    }

    /**
     * Get connect account detail
     *
     * @param string $account_id
     * @return Account
     * @throws ApiErrorException
     */
    public function retrieveConnectAccount(string $account_id): Account
    {
        try {

            // https://stripe.com/docs/api/accounts/retrieve
            return $this->stripe->accounts->retrieve($account_id);

        } catch (ApiErrorException $exception) {
            StripeAuditLog::auditRaw($exception, __FUNCTION__, self::mergeArrayWithRequestInputs(func_get_args()));
            throw  $exception;
        }
    }

    /**
     * Delete an account
     * https://stripe.com/docs/api/accounts/delete
     * Read more about it
     *
     * @param string $account_id
     * @return Account
     * @throws ApiErrorException
     */
    public function deleteConnectAccount(string $account_id): Account
    {
        try {

            // https://stripe.com/docs/api/accounts/delete
            return $this->stripe->accounts->delete($account_id);

        } catch (ApiErrorException $exception) {
            StripeAuditLog::auditRaw($exception, __FUNCTION__, self::mergeArrayWithRequestInputs(func_get_args()));
            throw  $exception;
        }
    }

    /**
     * Creates a single-use login link for an Express account to access their Stripe dashboard.
     * You may only create login links for Express accounts connected to your platform.
     *
     * @param string $account_id
     * @return LoginLink
     * @throws ApiErrorException
     */
    public function createExpressAccountLoginURL(string $account_id): LoginLink
    {
        try {

            // https://stripe.com/docs/api/account/create_login_link
            return $this->stripe->accounts->createLoginLink($account_id);

        } catch (ApiErrorException $exception) {
            StripeAuditLog::auditRaw($exception, __FUNCTION__, self::mergeArrayWithRequestInputs(func_get_args()));
            throw  $exception;
        }
    }

    /**
     *  Create express | custom connect accounts
     *  tos recipient or full
     *
     * @param string $first_name
     * @param string $last_name
     * @param string $email
     * @param int $user_id
     * @param string|null $phone
     * @param string $account_type
     * @param string $tos_acceptance
     * @return Account
     * @throws ApiErrorException
     */
    public function createConnectExpressOrCustomAccount(
        string  $first_name,
        string  $last_name,
        string  $email,
        int     $user_id,
        ?string $phone = null,
        string $account_type = 'express',
        string $tos_acceptance = "full",
        string $country = 'US'
    ): Account
    {

        try {
            // https://stripe.com/docs/api/accounts/create

            // Account once created can not be deleted. So, attach to user.
            return $this->stripe->accounts->create([
                'type' => $account_type,
                'email' => $email,

                'country' => $country,

                // only if type is custom
                // if card payment is requested, it requires more detail
                // also this can affect the progress of an account

                'capabilities' => [
                    'card_payments' => ['requested' => false],
                    'transfers' => ['requested' => true],
                ],

                // switching to recipient because of cross border transfers
                //https://stripe.com/docs/connect/service-agreement-types
                ['tos_acceptance' => ['service_agreement' => $tos_acceptance]],

                'business_type' => 'individual',
                'individual' => [
                    'first_name' => $first_name,
                    'last_name' => $last_name,
//                    'phone' => $phone,
                    'email' => $email,
                ],
                'business_profile' => [
                    'support_url' => env( 'STRIPE_CONNECT_FIX_URL' ),
                    'url' => env( 'STRIPE_CONNECT_FIX_URL' ),
                ],
                'company' => [
                    'name' => "$first_name $last_name",
//                    'phone' => $phone,
                ],
                'metadata' => [
                    'user_id' => $user_id,
                ]
            ]);
        } catch (ApiErrorException $exception) {
            StripeAuditLog::auditRaw($exception, __FUNCTION__, self::mergeArrayWithRequestInputs(func_get_args()));
            throw  $exception;
        }
    }

    /**
     *  Create standard
     *
     *  service agreement types: full or recipient
     * @param string $first_name
     * @param string $last_name
     * @param string $email
     * @param int $user_id
     * @param string|null $phone
     * @return Account
     * @throws ApiErrorException
     */
    public function createConnectStandardAccount(
        string  $first_name,
        string  $last_name,
        string  $email,
        int     $user_id,
        ?string $phone = null
    ): Account
    {

        try {
            // https://stripe.com/docs/api/accounts/create

            // Account once created can not be deleted. So, attach to user.
            return $this->stripe->accounts->create([
                'type' => 'standard',
                'email' => $email,

                // only if type is custom
                // if card payment is requested, it requires more detail
                // also this can affect the progress of an account

                'business_type' => 'individual',
                'individual' => [
                    'first_name' => $first_name,
                    'last_name' => $last_name,
//                    'phone' => $phone,
                    'email' => $email,
                ],
                'company' => [
                    'name' => "$first_name $last_name",
//                    'phone' => $phone,
                ],
                'metadata' => [
                    'user_id' => $user_id,
                ],

//
//              Not supported in some countries
//                string  $tos_acceptance = "recipient"
//                'tos_acceptance' => ['service_agreement' => $tos_acceptance]
            ]);
        } catch (ApiErrorException $exception) {
            StripeAuditLog::auditRaw($exception, __FUNCTION__, self::mergeArrayWithRequestInputs(func_get_args()));
            throw  $exception;
        }
    }

//    public function updateExpressConnectAccount(string $account_id): Account
//    {
//        // Account once created can not be deleted. So, attach to user.
//        $account = $this->stripe->accounts->update( $account_id,  [
//
////            'company' => [
////                'name' => 'Admin SCADWARE',
//////               'phone' => '+33666630760',
////               'address' =>[
////                        "city" => "Walpole",
////                      "country" => "US",
////                      "line1" => "1 Summit Ave",
////                      "line2" => null,
////                      "postal_code" => "02081",
////                      "state" => "MA"
////                    ]
////            ],
//            'capabilities' => [
//               'card_payments' => ['requested' => false ],
//               'transfers' => ['requested' => true],
//           ],
//        ]);
//
//        return $account;
//    }

//    public function collectAPayment(): PaymentIntent
//    {
//        $intent = $this->stripe->paymentIntents->create(
//            [
////                'payment_method_types' => ['card'],
//                'amount' => 10000,  // 100 USD
//                'currency' => 'usd',
//                'application_fee_amount' => 500, //5 USD
//
//                // don't transfer now
////                'transfer_data' => [
////                    'destination' => '{{CONNECTED_STRIPE_ACCOUNT_ID}}',
////                ],
//            ]
//        );
//
//        Log::info( "Intent Created. " . $intent->id, $intent->toArray() );
//
//        return $intent;
//    }

//
//    public function chargeCustomerCard( string $customerId , float $amount, string $account_id  ): Charge
//    {
//        $intent = $this->stripe->charges->create(
//            [
//                'amount' => self::calculateCharge( $amount ),  //  USD
//                'currency' => 'usd',
//
////                // this will only work if we know where it is going
////                'application_fee_amount' => 500, //5 USD
////                'on_behalf_of' => $account_id, //5 USD
//
////                'description' => "500, //5 USD"
//
//                // if you are using customer, the source must belong to the customer,
//                // the source is also optional, it will pick default.
//
//                'customer' => $customerId, // optional
//                // on customer object =>   "default_source": "card_1K6cPJHmKHsSEQwrZWn4xtRk",
//                'source' => 'card_1K6cPJHmKHsSEQwrZWn4xtRk'
//            ]
//        );
//
//        Log::info( "Charge Created. " . $intent->id, $intent->toArray() );
//
//        return $intent;
//    }

    /**
     *  Charge an Intent for connect
     * @param string $stripe_customer_id
     * @param string $customer_source_id
     * @param float $amount
     * @param string $destination_account_id
     * @return PaymentIntent
     * @throws ApiErrorException
     */
    public function chargeConnectPaymentIntent( string $stripe_customer_id , string $customer_source_id,
                                                float $amount, string $destination_account_id  ): PaymentIntent
    {
        try {


            // https://stripe.com/docs/payments/payment-intents
            // Java script can just invoke with client_secret
            // // Call stripe.confirmCardPayment() with the client secret.

            // make sure the destination account has transfers capability active.
            // else it will fail

            // https://stripe.com/en-fr/connect
            // https://stripe.com/docs/api/payment_intents/confirm

            return $this->stripe->paymentIntents->confirm(
                $this->stripe->paymentIntents->create(
                    [
                        'amount' => self::toCents($amount),  //  USD
                        'currency' => 'usd',

//      "doc_url" => "https://stripe.com/docs/error-codes/parameter-missing"
//      "message" => "Can only apply an application_fee_amount when the PaymentIntent is attempting a
//      direct payment (using an OAuth key or Stripe-Account header) or destination payment
//      (using `transfer_data[destination]`)."

//                // this will only work if we know where it is going
                        'application_fee_amount' => self::toCents(floatval(env('STRIPE_APPLICATION_FEE'))), //

                        // if you are using customer, the source must belong to the customer,
                        // the source is also optional, it will pick default.
                        'customer' => $stripe_customer_id, // optional
                        'source' => $customer_source_id,

//                        'on_behalf_of' => $destination_account_id,

                        'transfer_data' => [
                            'destination' => $destination_account_id,
                        ],
                    ]
                )->id
            );
        } catch (ApiErrorException $exception) {
            StripeAuditLog::auditRaw($exception, __FUNCTION__, self::mergeArrayWithRequestInputs(func_get_args()));
            throw  $exception;
        }
    }

    /**
     *  Charge an Intent for connect
     * @param string $stripe_customer_id
     * @param string $customer_source_id
     * @param float $amount
     * @param string $destination_account_id
     * @return PaymentIntent
     * @throws ApiErrorException
     */
    public function chargeExpressConnectPaymentIntent( string $stripe_customer_id , string $customer_source_id,
                                                       float $amount, string $destination_account_id  ): PaymentIntent
    {
        try {


            // https://stripe.com/docs/payments/payment-intents
            // Java script can just invoke with client_secret
            // // Call stripe.confirmCardPayment() with the client secret.

            // make sure the destination account has transfers capability active.
            // else it will fail

            // https://stripe.com/en-fr/connect
            // https://stripe.com/docs/api/payment_intents/confirm

            return $this->stripe->paymentIntents->confirm(
                $this->stripe->paymentIntents->create(
                    [
                        'amount' => self::toCents($amount),  //  USD
                        'currency' => 'usd',

//      "doc_url" => "https://stripe.com/docs/error-codes/parameter-missing"
//      "message" => "Can only apply an application_fee_amount when the PaymentIntent is attempting a
//      direct payment (using an OAuth key or Stripe-Account header) or destination payment
//      (using `transfer_data[destination]`)."

////                // this will only work if we know where it is going
///                 // this won't work if am using on_behalf_of
                        'application_fee_amount' => self::toCents(floatval(env('STRIPE_APPLICATION_FEE'))), //

                        // if you are using customer, the source must belong to the customer,
                        // the source is also optional, it will pick default.
                        'customer' => $stripe_customer_id, // optional
                        'source' => $customer_source_id,

////                      This doesn't work on express unless I request card capabilities as well
//                        'on_behalf_of' => $destination_account_id,

                        'transfer_data' => [
                            'destination' => $destination_account_id,
                        ],
                    ]
                )->id
            );
        } catch (ApiErrorException $exception) {
            StripeAuditLog::auditRaw($exception, __FUNCTION__, self::mergeArrayWithRequestInputs(func_get_args()));
            throw  $exception;
        }
    }
//
//    public function transferToConnectCustomerIntent( string $account_id ): \Stripe\Transfer
//    {
//        // https://stripe.com/docs/api/payment_intents/confirm
//        // // Call stripe.confirmCardPayment() with the client secret.
//        $intent = $this->stripe->transfers->create([
//            'amount' => 500,
//            'currency' => 'usd',
//            'destination' => $account_id
//        ]);
//
//        return $intent;
//    }
//
//    public function connectedAccountDebit(float $amount, string $account_id): Charge
//    {
//        // https://stripe.com/docs/connect/account-debits
//        return $this->stripe->charges->create(
//            [
//                'amount' => intval( $amount  * 100 ),  // 100 USD
//                'currency' => 'usd',
//                'source' => $account_id
//            ]
//        );
//    }


    /**
     *  Create an Intent
     *  Charges are drawn from platform
     *
     *
     * @param float $amount
     * @param string $stripe_customer_id
     * @return PaymentIntent
     * @throws ApiErrorException
     */
    public function createPaymentIntent( float $amount , string $stripe_customer_id,
                                         bool $confirm = false,
                                         ?string $payment_method = null,
                                         string $return_url = '/test'): PaymentIntent
    {
        try {

            // https://stripe.com/docs/payments/setup-intents
            // https://stripe.com/docs/payments/payment-intents
            // Java script can just invoke with client_secret
            // // Call stripe.confirmCardPayment() with the client secret.

            // https://stripe.com/docs/api/payment_intents/create
            // https://stripe.com/docs/api/payment_intents/confirm

            $query = [
                'amount' => self::toCents($amount),  //  USD
                'currency' => 'usd',

                'transfer_group' => sprintf( "%s-%s", Carbon::now()->timestamp  , Str::random() ),

                // I can add customer later to enable trace
//                    // if you are using customer, the source must belong to the customer,
//                    // the source is also optional, it will pick default.
                'customer' => $stripe_customer_id, // optional

                // this will just add the payment as a payment method
                //
                'setup_future_usage' => 'off_session',

                'confirm' => $confirm, // optional

                //  Use payment methods enabled in dashboard

                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ];
            if( $payment_method )
                $query['payment_method'] = $payment_method;

            // You must provide a `return_url` when confirming using automatic_payment_methods[enabled]=true.
            if( $confirm ) // this is required if you are auto confirming
                $query['return_url'] = self::buildCallBackURL($return_url);

            return $this->stripe->paymentIntents->create($query);
        } catch (ApiErrorException $exception) {
            StripeAuditLog::auditRaw($exception, __FUNCTION__, self::mergeArrayWithRequestInputs(func_get_args()));
            throw  $exception;
        }
    }

    /**
     *  Fetch an Intent
     *  Charges are drawn from platform
     *
     *
     * @param string $stripe_intent_id
     * @return PaymentIntent
     * @throws ApiErrorException
     */
    public function retrievePaymentIntent( string $stripe_intent_id ): PaymentIntent
    {
        try {

            // https://stripe.com/docs/api/payment_intents/object

            // https://stripe.com/docs/payments/intents#intent-statuses
            //
            // Status of this PaymentIntent, one of requires_payment_method, requires_confirmation,
            // requires_action, processing, requires_capture, canceled,
            // or [succeeded].

            return $this->stripe->paymentIntents->retrieve( $stripe_intent_id );
        } catch (ApiErrorException $exception) {
            StripeAuditLog::auditRaw($exception, __FUNCTION__, self::mergeArrayWithRequestInputs(func_get_args()));
            throw  $exception;
        }
    }

    /**
     *  Fetch an Intent
     *  Charges are drawn from platform
     *
     *
     * @param string $stripe_intent_id
     * @param string|null $payment_method
     * @param string $return_url
     * @return PaymentIntent
     * @throws ApiErrorException
     */
    public function confirmPaymentIntent( string $stripe_intent_id, ?string $payment_method = null, string $return_url = '/test'  ): PaymentIntent
    {
        try {

            // https://stripe.com/docs/api/payment_intents/confirm
            $query =  [
                'return_url' => self::buildCallBackURL($return_url)
            ];
            if( $payment_method )
                $query['payment_method'] = $payment_method;

            return $this->stripe->paymentIntents->confirm( $stripe_intent_id,$query);
        } catch (ApiErrorException $exception) {
            StripeAuditLog::auditRaw($exception, __FUNCTION__, self::mergeArrayWithRequestInputs(func_get_args()));
            throw  $exception;
        }
    }

    /**
     *  Fetch an Intent
     *  Charges are drawn from platform
     *
     *
     * @param string $stripe_intent_id
     * @param string|null $payment_method
     * @param string $return_url
     * @return PaymentIntent
     * @throws ApiErrorException
     */
    public function confirmPaymentIntentWithCard( string $stripe_intent_id, string $token = null, string $return_url = '/test'  ): PaymentIntent
    {
        try {

            // https://stripe.com/docs/api/payment_intents/confirm
            $query =  [
                'return_url' => self::buildCallBackURL($return_url),
                'payment_method_data' => [
                    "type"=>"card",
                     "card" => ["token" => $token ]
                ]
            ];

            return $this->stripe->paymentIntents->confirm( $stripe_intent_id,$query);
        } catch (ApiErrorException $exception) {
            StripeAuditLog::auditRaw($exception, __FUNCTION__, self::mergeArrayWithRequestInputs(func_get_args()));
            throw  $exception;
        }
    }

    /**
     *  Move money to another account
     *  Charges are drawn from platform
     *
     *
     * @param float $amount
     * @param string $stripe_connect_account_id
     * @param string|null $stripe_related_charge_id
     * @param string|null $transfer_group
     * @return Transfer
     * @throws ApiErrorException
     */
    public function createTransfer( float $amount, string $stripe_connect_account_id,
                                    string $stripe_related_charge_id = null,
                                    string $transfer_group = null  ): Transfer
    {
        try {

            // https://stripe.com/docs/api/transfers/create#create_transfer-source_transaction

            // Free transfer
            // Not working for standard accounts
            //
            // Works Only with Express accounts with transfer capability
            //https://stripe.com/docs/connect/charges-transfers

            $query =  [
                'amount' => self::toCents($amount),
                'currency' => 'usd',
                'destination' => $stripe_connect_account_id,
                'transfer_group' => $transfer_group,
            ];
            if( $stripe_related_charge_id )
                $query['source_transaction'] = $stripe_related_charge_id;

            return $this->stripe->transfers->create($query);
        } catch (ApiErrorException $exception) {
            StripeAuditLog::auditRaw($exception, __FUNCTION__, self::mergeArrayWithRequestInputs(func_get_args()));
            throw  $exception;
        }
    }

    /**
     *  Creating a new refund will refund a charge that has previously been created
     *  but not yet refunded. Funds will be refunded to the credit or debit card
     *  that was originally charged
     *
     * @param string $stripe_charge_id
     * @return \Stripe\Refund
     * @throws ApiErrorException
     */
    public function createRefund( string $stripe_charge_id): \Stripe\Refund
    {
        try {

            // https://stripe.com/docs/api/refunds/create

            return $this->stripe->refunds->create(
                [
                    'charge' => $stripe_charge_id,
                ]
            );
        } catch (ApiErrorException $exception) {
            StripeAuditLog::auditRaw($exception, __FUNCTION__, self::mergeArrayWithRequestInputs(func_get_args()));
            throw  $exception;
        }
    }
}
