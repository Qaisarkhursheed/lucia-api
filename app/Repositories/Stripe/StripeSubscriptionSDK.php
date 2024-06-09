<?php

namespace App\Repositories\Stripe;

use App\ModelsExtended\StripeAuditLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Stripe\BankAccount;
use Stripe\Card;
use Stripe\Charge;
use Stripe\Checkout\Session;
use Stripe\Collection;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\Invoice;
use Stripe\PaymentMethod;
use Stripe\Price;
use Stripe\Product;
use Stripe\Source;
use Stripe\StripeClient;
use Stripe\StripeObject;
use Stripe\Subscription;
use Stripe\Token;

class StripeSubscriptionSDK
{
    /**
     * @var StripeClient
     */
    protected StripeClient $stripe;

    public function __construct()
    {
        // Set your secret key. Remember to switch to your live secret key in production.
        // See your keys here: https://dashboard.stripe.com/apikeys
        $this->stripe = new StripeClient(env('STRIPE_SECRET_KEY' ));
    }

    /**
     * @param Card|StripeObject $card
     * @return array
     */
    public static function formatCardForDisplay( $card): array
    {
        if( $card instanceof StripeObject)
        {
            $card = $card->toArray();
            return [
                'stripe_token_id' => null,
                "brand" => $card["brand"],
                "country" => $card["country"],
                "name" => null,
                "address_country" => null,
                "address_state" => $card["checks"]["address_line1_check"],
                "address_zip" => $card["checks"]["address_postal_code_check"],
                "cvc_check" => $card["checks"]["cvc_check"],
                "exp_month" => $card["exp_month"],
                "exp_year" => $card["exp_year"],
                "funding" => $card["funding"],
                "last4" => $card["last4"],
            ];
        }

        return [
            'stripe_token_id' => $card->id,
            "brand" => strtolower($card->brand),
            "country" => $card->country,
            "name" => $card->name,
            "address_country" => $card->address_country,
            "address_state" => $card->address_state,
            "address_zip" => $card->address_zip,
            "cvc_check" => $card->cvc_check,
            "exp_month" => $card->exp_month,
            "exp_year" => $card->exp_year,
            "funding" => $card->funding,
            "last4" => $card->last4,
        ];
    }

    /**
     * @param Source|Card|BankAccount $source
     * @return array
     */
    public static function formatSourceForDisplay( $source): array
    {
        if( $source instanceof Card ) return self::formatCardForDisplay( $source );
        if( $source instanceof BankAccount ) return self::formatBankAccountForDisplay( $source );
        return [];
    }

    /**
     * @param BankAccount $bank
     * @return array
     */
    public static function formatBankAccountForDisplay(BankAccount $bank): array
    {
        return [
            'stripe_token_id' => $bank->id,
            "account_holder_name" => $bank->account_holder_name,
            "account_holder_type" => $bank->account_holder_type,
            "account_type" => $bank->account_type,
            "bank_name" => $bank->bank_name,
            "country" => $bank->country,
            "currency" => $bank->currency,
            "routing_number" => $bank->routing_number,
            "last4" => $bank->last4,
            "status" => $bank->status, // must be verified to be used
        ];
    }

    /**
     * @param Invoice $invoice
     * @return array
     */
    public static function formatInvoiceForDisplay(Invoice $invoice): array
    {
        return [
            "id" => $invoice->id,
            "status" => $invoice->status,
            "invoice_pdf" => $invoice->invoice_pdf,
            "amount_remaining" => $invoice->amount_remaining/100,
            "amount_paid" => $invoice->amount_paid/100,
            "currency" => $invoice->currency,
            "created_at" => Carbon::createFromTimestamp($invoice->created),
        ];
    }

    /**
     * @param array $inputs
     * @return array
     */
    protected static function mergeArrayWithRequestInputs(array $inputs = []): array
    {
        $request = request();
        $x = $request? $request->all(): [];
        return array_merge( $inputs, $x );
    }

    /**
     * This is to just create a customer on your account.
     * Not related to connect.
     *
     * @param string $name
     * @param string $email
     * @param string|null $description
     * @param string|null $phone
     * @return Customer
     * @throws ApiErrorException
     */
    public function createCustomer( string $name,
                                    string  $email,
                                    ?string  $description = null,
                                    ?string $phone = null
    ): Customer
    {
        try {

            // https://stripe.com/docs/api/customers/object
            return $this->stripe->customers->create(
                [
                    'description' => $description,  // Optional
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone
                ]
            );

        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    /**
     * @param string $stripe_customer_id
     * @param string $name
     * @param string $email
     * @param string|null $description
     * @param string|null $phone
     * @return Customer
     * @throws ApiErrorException
     */
    public function updateCustomer( string $stripe_customer_id,
                                    string $name,
                                    string  $email,
                                    ?string  $description = null,
                                    ?string $phone = null
    ): Customer
    {
        try {

            return $this->stripe->customers->update( $stripe_customer_id,
                [
                    'description' => $description,  // Optional
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone
                ]
            );

        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    /**
     * @param string $stripe_customer_id
     * @return Customer
     * @throws ApiErrorException
     */
    public function deleteCustomer( string $stripe_customer_id): Customer
    {
        try {

            return $this->stripe->customers->delete( $stripe_customer_id);

        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    /**
     *
     * @param string $stripe_customer_id
     * @return Customer|null
     * @throws ApiErrorException
     */
    public function getCustomer( string $stripe_customer_id ): ?Customer
    {
        try {

            return $this->stripe->customers->retrieve( $stripe_customer_id );

        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    /**
     * search a customer by email
     *
     * @param string $email
     * @return Customer|null
     * @throws ApiErrorException
     */
    public function getCustomerByEmail( string $email ): ?Customer
    {
        try {

            // https://stripe.com/docs/api/products/retrieve
            return collect(
                $this->stripe->customers->all( [
                    "email" => $email
                ])->data
            )->first();

        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    /**
     * @param string $stripe_customer_id
     * @param string $stripe_card_id
     * @return Customer
     * @throws ApiErrorException
     */
    public function updateCustomerDefaultPayment( string $stripe_customer_id, string $stripe_card_id    ): Customer
    {
        try {

            return $this->stripe->customers->update( $stripe_customer_id,
                [
                    'default_source' => $stripe_card_id
                ]
            );

        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    /**
     * Focus on getting Payment Method Object in Array
     * @param string $stripe_customer_id
     * @return Collection
     * @throws ApiErrorException
     */
    public function getCustomersCardPaymentMethods(string $stripe_customer_id ): Collection
    {
        try {
            return $this->stripe->customers->allPaymentMethods(
                $stripe_customer_id,
                ['type' => 'card']
            );
        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    /**
     * @param string $stripe_customer_id
     * @param string $number
     * @param int $exp_month
     * @param int $exp_year
     * @param int $cvc
     * @return PaymentMethod
     * @throws ApiErrorException
     */
    public function createPaymentMethod( string $stripe_customer_id,
                                         string $number, int $exp_month,
                                         int $exp_year, int $cvc ): PaymentMethod
    {
        try {
            // we can create payment methods and save on stripe account locally
            // https://stripe.com/docs/api/payment_methods/create
            $intent = $this->stripe->paymentMethods->create(
                [
                    'type' => 'card',
                    'card' => [
                        'number' => $number,
                        'exp_month' => $exp_month,
                        'exp_year' => $exp_year,
                        'cvc' => $cvc,
                    ],
                ]
            );

            return $this->stripe->paymentMethods->attach(
                $intent->id,
                ['customer' => $stripe_customer_id ]
            );
        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }


    /**
     * Focus on getting just Card Objects in array
     *
     * @param string $stripe_customer_id
     * @return \Illuminate\Support\Collection
     * @throws ApiErrorException
     */
    public function listAllCardPaymentMethods(string $stripe_customer_id ): \Illuminate\Support\Collection
    {
        try {

            return collect(
                    $this->stripe->paymentMethods->all([
                        'customer' => $stripe_customer_id,
                        'type' => 'card',
                        'limit' => 50
                ])->data
            )->map(function (PaymentMethod $item){
                $billing_details = $item->billing_details->toArray();
                return array_merge( self::formatCardForDisplay( $item->card), [
                        "stripe_token_id" => $item->id,
                           "name" => $billing_details["name"],
                          "address_country" => $billing_details["address"]["country"],
                          "address_state" => $billing_details["address"]["state"],
                          "address_zip" => $billing_details["address"]["postal_code"],
                       ]
                );
            });

        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }



//    /**
//     *
//     * Focus on getting just Card Objects in array
//     * This is not actually retrieving all cards
//     * Use payment method instead
//     *
//     * @deprecated
//     * @param string $stripe_customer_id
//     * @return Collection
//     * @throws ApiErrorException
//     */
//    public function getCustomersCards(string $stripe_customer_id ): Collection
//    {
//        try {
//
//            return $this->stripe->customers->allSources(
//                $stripe_customer_id,
//                ['object' => 'card', 'limit' => 50 ]
//            );
//        }catch (ApiErrorException $exception)
//        {
//            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
//            throw  $exception;
//        }
//    }

//    /**
//     * This is not getting all the sources completely
//     * Use payment method listing instead
//     *
//     * @deprecated
//     * @param string $stripe_customer_id
//     * @return \Illuminate\Support\Collection
//     * @throws ApiErrorException
//     */
//    public function getCustomerSources(string $stripe_customer_id ): \Illuminate\Support\Collection
//    {
//        try {
//
//            $collection = $this->getCustomersCards($stripe_customer_id);
//
//            $cards =  collect( $collection->data )
//                ->toArray();
//
//            $collection = $this->getCustomersBankAccounts($stripe_customer_id);
//            $banks =  collect( $collection->data )
//                ->toArray();
//
//            return collect(array_merge( $cards, $banks ));
//        }catch (ApiErrorException $exception)
//        {
//            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
//            throw  $exception;
//        }
//    }

//    /**
//     * This is not working perfectly because it is dependent on a deprecated method
//     *
//     * @deprecated
//     * @param string $stripe_customer_id
//     * @param string $stripe_source_id
//     * @return Source|Card|BankAccount
//     * @throws ApiErrorException
//     */
//    public function getCustomerSource(string $stripe_customer_id, string $stripe_source_id )
//    {
//        try {
//
//            return $this->getCustomerSources( $stripe_customer_id )
//                /**
//                 * Source|Card|Bank
//                 */
//                ->filter( function ( $source ) use ($stripe_source_id){
//                    return $source->id === $stripe_source_id;
//                } )
//                ->first();
//
//        }catch (ApiErrorException $exception)
//        {
//            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
//            throw  $exception;
//        }
//    }

    /**
     * Focus on getting just Card Objects in array
     *
     * @param string $stripe_customer_id
     * @return Collection|BankAccount[]
     * @throws ApiErrorException
     */
    public function getCustomersBankAccounts(string $stripe_customer_id ): Collection
    {
        try {
            // https://stripe.com/docs/api/payment_methods/list
            // stripe doesn't have a way to fetch all the list together
            return $this->stripe->customers->allSources(
                $stripe_customer_id,
                ['object' => 'bank_account', 'limit' => 50 ]
            );
        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    /**
     *
     *
     * @param string $payment_method_id
     * @return PaymentMethod
     * @throws ApiErrorException
     */
    public function retrievePaymentMethod(string $payment_method_id ): PaymentMethod
    {
        try {
            // https://stripe.com/docs/api/payment_methods/list
            // stripe doesn't have a way to fetch all the list together
            return $this->stripe->paymentMethods->retrieve($payment_method_id);
        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    /**
     * @param string $stripe_customer_id
     * @param string $token
     * @return Card|\Stripe\Source|BankAccount
     * @throws ApiErrorException
     */
    public function createPaymentSourceForCustomer(string $stripe_customer_id, string $token )
    {
        try {
            // we can create payment methods and save on stripe account locally
            // https://stripe.com/docs/api/payment_methods/create

            // use source instead
            // https://stripe.com/docs/api/customer_bank_accounts/create
            return $this->stripe->customers->createSource(
                $stripe_customer_id,
                [
                    'source' => $token
                ]
            );
        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    /**
     * @param string $stripe_customer_id
     * @param string $stripe_card_id
     * @return Card|\Stripe\Source
     * @throws ApiErrorException
     */
    public function deleteSource( string $stripe_customer_id,  string $stripe_card_id )
    {
        try {
            // we can create payment methods and save on stripe account locally
            // https://stripe.com/docs/api/payment_methods/create
            return $this->stripe->customers->deleteSource(
                $stripe_customer_id, $stripe_card_id
            );
        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    /**
     * @param string $number
     * @param int $exp_month
     * @param int $exp_year
     * @param int $cvc
     * @return Token
     * @throws ApiErrorException
     */
    public function createCardToken( string $number, int $exp_month,
                                     int $exp_year, int $cvc): Token
    {
        try {
            // we can create token to use for payment if we store the cards locally
            // https://stripe.com/docs/api/tokens/create_card
            return $this->stripe->tokens->create(
                [
                    'card' => [
                        'number' => $number,
                        'exp_month' => $exp_month,
                        'exp_year' => $exp_year,
                        'cvc' => $cvc,
                    ],
                ]
            );
        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    /**
     * @param string $account_holder_name
     * @param string $account_number
     * @param string $routing_number
     * @param string $country
     * @param string $currency
     * @param string $account_holder_type
     * @return Token
     * @throws ApiErrorException
     */
    public function createBankToken( string $account_holder_name, string $account_number, string $routing_number,
                                     string $country = 'US', string $currency = 'USD',
                                     string $account_holder_type='individual'): Token
    {
        try {
            // we can create token to use for payment if we store the cards locally
            // https://stripe.com/docs/api/tokens/create_bank_account
            return $this->stripe->tokens->create(
                [
                    'bank_account' => [
                        'country' => $country,
                        'currency' => $currency,
                        'account_holder_name' => $account_holder_name,
                        'account_holder_type' => $account_holder_type,
                        'routing_number' => $routing_number,
                        'account_number' => $account_number,
                    ],
                ]
            );
        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    /**
     * @param string $customer_id
     * @param string $bank_account_id
     * @param array $amounts
     * @return \Stripe\AlipayAccount|BankAccount|\Stripe\BitcoinReceiver|Card|Source
     * @throws ApiErrorException
     */
    public function verifyBankAccount( string $customer_id, string $bank_account_id, array $amounts)
    {
        try {


            // Bank Debits
            // https://stripe.com/docs/payments/bank-debits
            // because payment confirmation takes 3-7 days.

            // To receive money you can create an intent with bank options
            // Also, the intent can be set to verify the bank immediately.
            // https://stripe.com/docs/payments/ach-debit/accept-a-payment?platform=mobile&ui=react-native#microdeposit-only-verification


            // Customer bank accounts require verification.
            // When using Stripe without Plaid, Stripe automatically sends two small deposits for this purpose.
            // These deposits take 1-2 business days to appear on the customer’s online statement.
            // The statement has a description that includes AMTS followed by the two microdeposit amounts.
            // Your customer must relay these amounts to you.

            // When accepting these amounts, be aware that the limit is three failed verification attempts.

            // https://stripe.com/docs/ach-deprecated#manually-collecting-and-verifying-bank-accounts
            // https://stripe.com/docs/api/customer_bank_accounts/verify
            // https://stripe.com/docs/ach-deprecated#testing-ach

            return $this->stripe->customers->verifySource(
                $customer_id,
                $bank_account_id,
                ['amounts' => $amounts]
            );

        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    /**
     * @param string $stripe_customer_id
     * @param string $token
     * @return Card|\Stripe\Source
     * @throws ApiErrorException
     */
    public function createCard( string $stripe_customer_id,  string $token )
    {
        try {
            // we can create payment methods and save on stripe account locally
            // https://stripe.com/docs/api/payment_methods/create
            return $this->stripe->customers->createSource(
                $stripe_customer_id,
                [
                    'source' => $token
                ]
            );
        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    /**
     * @param string $stripe_customer_id
     * @param string $stripe_card_id
     * @return Card|\Stripe\Source
     * @throws ApiErrorException
     */
    public function deleteCard( string $stripe_customer_id,  string $stripe_card_id )
    {
        try {
            // we can create payment methods and save on stripe account locally
            // https://stripe.com/docs/api/payment_methods/create
            return $this->stripe->customers->deleteSource(
                $stripe_customer_id, $stripe_card_id
            );
        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    /**
     * Create a product
     *
     * @param string $name
     * @param string $description
     * @param bool $active
     * @return Product
     * @throws ApiErrorException
     */
    public function createProduct( string $name, string $description, bool $active = true,
                                   string $url = "https://www.letslucia.com/",
                                   string $icon_url = "https://app.letslucia.com/logo-lucia-l-letter.png"
    ): Product
    {
        try {

            // https://stripe.com/docs/api/products/create
            return $this->stripe->products->create(
                [
                    'description' => $description,  // Optional
                    'name' => $name,
                    'active' => $active,
                    'url' => $url,
                    "images" => [ $icon_url ],
                ]
            );

        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    /**
     * Change Active status of a product
     *
     * @param string $stripe_product_id
     * @param bool $active
     * @return Product
     * @throws ApiErrorException
     */
    public function updateProduct(string $stripe_product_id, bool $active = true,
                                  string $url = "https://www.letslucia.com/",
                                  string $icon_url = "https://app.letslucia.com/logo-lucia-l-letter.png"
    ): Product
    {
        try {

            // https://stripe.com/docs/api/products/update
            return $this->stripe->products->update(
                $stripe_product_id,
                [
                    'active' => $active,
//                    'url' => $url,
//                    "images" => [ $icon_url ],
                ]
            );

        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    /**
     * Change Active status of a product
     *
     * @param string $stripe_product_id
     * @param bool $active
     * @return Product
     * @throws ApiErrorException
     */
    public function setProductActive(string $stripe_product_id, bool $active = true ): Product
    {
        try {

            // https://stripe.com/docs/api/products/update
            return $this->stripe->products->update(
                $stripe_product_id,
                [
                    'active' => $active
                ]
            );

        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    /**
     * search a product by id
     *
     * @param string $stripe_product_id
     * @return Product
     * @throws ApiErrorException
     */
    public function getProductById( string $stripe_product_id ): Product
    {
        try {

            // https://stripe.com/docs/api/products/retrieve
            return $this->stripe->products->retrieve( $stripe_product_id);

        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    /**
     * search a product by name
     *
     * @param string $stripe_product_name
     * @return Product|null
     * @throws ApiErrorException
     */
    public function getProductByName( string $stripe_product_name ): ?Product
    {
        try {

            // https://stripe.com/docs/api/products/list
            return collect( $this->stripe->products->all()->data )->filter( function ( Product $product ) use ( $stripe_product_name ){
                return $product->name == $stripe_product_name;
            } )
                ->first();

        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }

    }

    /**
     * search a price by id
     *
     * @param string $stripe_price_id
     * @return Price
     * @throws ApiErrorException
     */
    public function getPriceById( string $stripe_price_id ): Price
    {
        try {

            // https://stripe.com/docs/api/prices/retrieve
            return $this->stripe->prices->retrieve( $stripe_price_id);

        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    /**
     * search a price by detail
     *
     * @param float $unit_amount
     * @param string $interval
     * @param string $currency
     * @return Price|null
     * @throws ApiErrorException
     */
    public function getPriceByDetail( float $unit_amount, string $interval = 'month', string $currency = 'USD' ): ?Price
    {
        try {

            // https://stripe.com/docs/api/products/list
            return collect( $this->stripe->prices->all()->data )->filter( function ( Price $price ) use ( $unit_amount, $interval, $currency ){
                return
                    $price->unit_amount == intval( $unit_amount * 100 )
                    && $price->recurring->interval == $interval
                    && strtolower( $price->currency ) == strtolower( $currency );
            } )
                ->first();

        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    /**
     * @param float $unit_amount
     * @param string $stripe_product_id
     * @param string $interval
     * @param string $currency
     * @return Price
     * @throws ApiErrorException
     */
    public function createPrice( float $unit_amount, string $stripe_product_id, string $interval = 'month', string $currency = 'USD'): Price
    {
        try {

            // https://stripe.com/docs/api/prices/create
            return $this->stripe->prices->create(
                [
                    'unit_amount' => intval( $unit_amount * 100 ),
                    'currency' => $currency,
                    // recurring.interval //REQUIRED
                    //Specifies billing frequency. Either day, week, month or year.

                    'recurring' => ['interval' => $interval],
                    'active' => true,
                    'product' => $stripe_product_id,
                ]
            );

        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    /**
     * @param string $stripe_price_id
     * @param string $stripe_customer_id
     * @param string $cancel_full_url
     * @param string $auth_token
     * @param string $success_relative_url
     * @param array|null $default_tax_rates
     * @param int|null $trial_days
     * @return Session
     * @throws ApiErrorException
     */
    public function createCheckout(string $stripe_price_id, string $stripe_customer_id,
                                   string $cancel_full_url,
                                   string $auth_token,
                                   string $success_relative_url = '/agent/license/checkout-successful',
                                   ?array  $default_tax_rates = [],
                                   ?int   $trial_days = null
    ): Session
    {
        try {

            $callBackUrl = Str::of( env( 'APP_URL' ) )->rtrim( "/" )
                . Str::of($success_relative_url )->start( "/");

            // https://stripe.com/docs/billing/subscriptions/build-subscription
            // https://stripe.com/docs/api/checkout/sessions/create

            $requests = [
                'success_url' => $callBackUrl . '?session_id={CHECKOUT_SESSION_ID}&token=' . $auth_token,
                'cancel_url' => $cancel_full_url,
                'customer' => $stripe_customer_id,
                'mode' => 'subscription',
                'allow_promotion_codes' => true,
                'line_items' => [[
                    'price' => $stripe_price_id,
                    // For metered billing, do not pass quantity
                    'quantity' => 1,
                ]],
                'subscription_data' => []
            ];

            // https://stripe.com/docs/billing/subscriptions/taxes
            if( $default_tax_rates && count( $default_tax_rates ) )
                $requests [ 'subscription_data' ]['default_tax_rates'] = $default_tax_rates;

            // add trial to it
            if( $trial_days && $trial_days > 0 )
                $requests [ 'subscription_data' ]['trial_period_days'] = $trial_days;

            return $this->stripe->checkout->sessions->create( $requests );

        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    /**
     * @param string $session_id
     * @return Session
     * @throws ApiErrorException
     */
    public function retrieveCheckout( string $session_id ): Session
    {
        try {

            // https://stripe.com/docs/payments/checkout/custom-success-page
            return $this->stripe->checkout->sessions->retrieve( $session_id );

        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    /**
     * @param string $stripe_customer_id
     * @param string $cancel_full_url
     * @return \Stripe\BillingPortal\Session
     * @throws ApiErrorException
     */
    public function createBillingPortal( string $stripe_customer_id, string $cancel_full_url ): \Stripe\BillingPortal\Session
    {
        try {

            // https://stripe.com/docs/payments/checkout/custom-success-page
            return $this->stripe->billingPortal->sessions->create( [
                [
                    'customer' => $stripe_customer_id,
                    'return_url' => $cancel_full_url,
                ]
            ] );

        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    /**
     * @param string $stripe_price_id
     * @param string $stripe_customer_id
     * @param string|null $default_payment_method
     * @param int|null $trial_period_days
     * @return Subscription
     * @throws ApiErrorException
     * @throws CustomApiInitiatedException
     */
    public function createSubscription( string $stripe_price_id, string $stripe_customer_id,
                                        ?string $default_payment_method,
                                        ?int $trial_period_days = null,
                                        array $metadata = []
                            ): Subscription
    {
        try {

            // https://stripe.com/docs/api/subscriptions/create
            // Customer must have payment method, but you can allow customer
            // to choose from list of payment methods

            //  "status": "active",
            //  "status": "incomplete", if failed
            $params = collect([
                'customer' => $stripe_customer_id,

                // you can set this to true to end a subscription
                'cancel_at_period_end' => false,

                'items' => [
                    ['price' => $stripe_price_id ],
                ]
            ]);

            // optional, you don't have to add it, it will auto select it
            if( $default_payment_method )
                $params->put(
                // we will pick it from customer->default_source
                    'default_payment_method' , $default_payment_method
                );

            if( $trial_period_days && $trial_period_days > 0 )
                $params->put(
                // It will add trial to this subscription and the customer won't be charged until then
                    'trial_period_days' , $trial_period_days
                );

            if( $metadata && count($metadata) )
                $params->put(
                //https://stripe.com/docs/api/metadata
                    'metadata' , $metadata
                );

            // https://stripe.com/docs/api/subscriptions/update
            return $this->stripe->subscriptions->create($params->toArray());

            //  This is not needed since user handles responses including trials
            //  if( $subscription->status !== 'active' )
            //     throw new CustomApiInitiatedException( 'Can not initiate subscription' , $subscription->toArray() );

        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }


    public function updateSubscription(string $stripe_subscription_id,array $updateParams =[]){
        return $this->stripe->subscriptions->update($stripe_subscription_id, ['items' => [$updateParams]]);
    }

    /**
     * @param string $stripe_subscription_id
     * @return Subscription
     * @throws ApiErrorException
     */
    public function cancelSubscription( string $stripe_subscription_id ): Subscription
    {
        try {

            // https://stripe.com/docs/api/subscriptions/update
            return $this->stripe->subscriptions->cancel( $stripe_subscription_id );

        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    /**
     * @param string $stripe_subscription_id
     * @return Subscription
     * @throws ApiErrorException
     */
    public function fetchSubscription( string $stripe_subscription_id ): Subscription
    {
        try {

            // https://stripe.com/docs/api/subscriptions/update
            return $this->stripe->subscriptions->retrieve( $stripe_subscription_id );

        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    /**
     * @param string $stripe_customer_id
     * @return array|null
     * @throws ApiErrorException
     */
    public function fetchCustomerSubscriptions( string $stripe_customer_id ): ?array
    {
        try {

            // https://stripe.com/docs/api/subscriptions/list
            return $this->stripe->subscriptions->all( [
                'customer' => $stripe_customer_id
            ] )->data;

        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    /**
     * @param string $invoice_id
     * @return Invoice
     * @throws ApiErrorException
     */
    public function fetchInvoice( string $invoice_id): Invoice
    {
        try {

            // https://stripe.com/docs/api/invoices/list
            // https://stripe.com/docs/api/invoices/pay
            // https://stripe.com/docs/api/invoices/send

            // https://stripe.com/docs/api/subscriptions/update
            return $this->stripe->invoices->retrieve($invoice_id);

        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    /**
     * @param string|null $stripe_customer_id
     * @return \Stripe\StripeObject[]
     * @throws ApiErrorException
     */
    public function listAllInvoices(?string $stripe_customer_id): array
    {
        try {

            // https://stripe.com/docs/api/invoices/list

            return $this->stripe->invoices->all([
//                'status' => 'open',
                'customer' => $stripe_customer_id,
                'limit' => 100  // exhaust in 8 years
            ])->data;

        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    /**
     * @param string|null $stripe_customer_id
     * @return \Stripe\StripeObject[]
     * @throws ApiErrorException
     */
    public function listPendingInvoices(?string $stripe_customer_id): array
    {
        try {

            // https://stripe.com/docs/api/invoices/list

            return $this->stripe->invoices->all([
                'status' => 'open',
                'customer' => $stripe_customer_id,
                'limit' => 100  // exhaust in 8 years
            ])->data;

        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    /**
     *  These are invoices that failed after several automatic retries
     *
     * @param string|null $stripe_customer_id
     * @return \Stripe\StripeObject[]
     * @throws ApiErrorException
     */
    public function listUncollectibleInvoices(?string $stripe_customer_id): array
    {
        try {

            // https://stripe.com/docs/api/invoices/list

            return $this->stripe->invoices->all([
                'status' => 'uncollectible',
                'customer' => $stripe_customer_id,
                'limit' => 100  // exhaust in 8 years
            ])->data;

        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    /**
     * @param string $stripe_invoice_id
     * @return Invoice
     * @throws ApiErrorException
     */
    public function payInvoice(string $stripe_invoice_id): Invoice
    {
        try {

            // https://stripe.com/docs/api/invoices/pay

            return $this->stripe->invoices->pay( $stripe_invoice_id );

        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    /**
     *  Use for adding calculations of bills with application fee
     *  to make sure amount charged customer covers the bills
     *  https://stripe.com/fr/pricing
     *
     * @param float $real_amount
     * @return float
     */
    public function addStripeFeeExclusive( float $real_amount ): float
    {
        // 2.9% + 0.30
        // (0.029 A + 0.3 ) = Y(1-0.029)
        // Y = Exclusive Fee
        // A = Amount Expected

        $fee = ( ( $real_amount * (2.9/100) )  + 0.30 ) / ( 1-0.029 );
        return $real_amount + $fee;
    }


    /**
     * @param float $val
     * @return int
     */
    public static function toCents(float $val): int
    {
        return intval( $val  * 100);
    }

    /**
     *  Charge a source of an amount
     *
     * @param float $amount
     * @param string $stripe_source
     * @param string|null $description
     * @return Charge
     * @throws ApiErrorException
     */
    public function chargeCard( float $amount, string $stripe_source, ?string $description = null ): Charge
    {
        try {

            // https://stripe.com/docs/api/charges/create

            return $this->stripe->charges->create(
                [
                    'amount' => self::toCents( $amount ),  //  USD
                    'currency' => 'usd',
                    'description' => $description,

                    'source' => $stripe_source
                ]
            );

        }catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
            throw  $exception;
        }
    }

    public function upcomingInvoice(string $customerID){
        try {

        return $this->stripe->invoices->upcoming([
            'customer' => $customerID,
          ]);
        }
        catch (ApiErrorException $exception)
        {
            StripeAuditLog::auditRaw( $exception, __FUNCTION__, self::mergeArrayWithRequestInputs( func_get_args() ) );
           // throw  $exception;
        }
    }
}
