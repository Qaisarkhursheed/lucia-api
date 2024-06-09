<?php

namespace App\Console\Commands\Payments;

use App\ModelsExtended\ApplicationProductPrice;
use App\ModelsExtended\ApplicationProduct;
use App\Repositories\Stripe\StripeSubscriptionSDK;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Stripe\Price;
use Stripe\Product;

class SetupParameters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:setup-params';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will setup all parameters needed for stripe payments';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // fetch all products
        $this->withProgressBar( ApplicationProduct::all(), function ( ApplicationProduct $product ){

            // update or create product in stripe
            if( $this->updateProductOnStripe( $product ) )
            {
                $this->info( "\nCreating prices for " . $product->name );

                // update or create product prices in stripe
                $this->withProgressBar( $product->application_product_prices, function ( ApplicationProductPrice $price ) {
                     $this->updateProductPrice( $price );
                });

                $this->info( "\n");
            }

        });

        $this->info( "\n-------------------------------------" );
        $this->info( "COMPLETED" );

        return true;
    }

    /**
     * @param ApplicationProduct $product
     * @return bool
     */
    private function updateProductOnStripe(ApplicationProduct $product): bool
    {
        $sdk = new StripeSubscriptionSDK();
        try {

            $stripeProduct = $this->getStripeProduct( $product , $sdk);
            if( ! $stripeProduct  )
            {
                $stripeProduct = $sdk->createProduct( $product->name, $product->description  );
            }else
                $stripeProduct = $sdk->updateProduct( $stripeProduct->id );

            $product->stripe_product_id = $stripeProduct->id;
            $product->stripe_product = $stripeProduct->toArray();
            $product->updateQuietly();

            return true;
        }catch (\Exception $exception ){
            $this->error( "Error Updating/Creating Product" );
            Log::error( $exception->getMessage() , $exception->getTrace() );
        }

        return false;
    }

    /**
     * @param ApplicationProduct $product
     * @param StripeSubscriptionSDK $sdk
     * @return Product|null
     * @throws ApiErrorException
     */
    private function getStripeProduct(ApplicationProduct $product, StripeSubscriptionSDK $sdk): ?Product
    {
        try {

            if( $product->stripe_product_id )
                return $sdk->getProductById( $product->stripe_product_id );

        }catch (ApiErrorException $exception ){}

        return $sdk->getProductByName( $product->name );
    }

    /**
     * @param ApplicationProductPrice $price
     * @return void
     */
    private function updateProductPrice(ApplicationProductPrice $price): void
    {
        $sdk = new StripeSubscriptionSDK();
        try {

            $stripePrice = $this->getStripePrice( $price , $sdk);
            if( ! $stripePrice  )
            {
                $stripePrice = $sdk->createPrice( $price->unit_amount, $price->application_product->stripe_product_id, $price->recurring);
            }

            $price->stripe_price_id = $stripePrice->id;
            $price->stripe_price = $stripePrice->toArray();
            $price->updateQuietly();

        }catch (\Exception $exception ){
            $this->error( "Error Updating/Creating Price" );
            Log::error( $exception->getMessage() , $exception->getTrace() );
        }
    }

    /**
     * @param ApplicationProductPrice $price
     * @param StripeSubscriptionSDK $sdk
     * @return Price|null
     */
    private function getStripePrice(ApplicationProductPrice $price, StripeSubscriptionSDK $sdk): ?Price
    {
        try {

            if( $price->stripe_price_id && ( $byId = $sdk->getPriceById( $price->stripe_price_id ) ) )
                return $byId;

            return $sdk->getPriceByDetail( $price->unit_amount, $price->recurring );

        }catch (ApiErrorException $exception ){}

        return null;
    }
}
