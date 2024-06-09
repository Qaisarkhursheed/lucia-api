<?php

namespace App\Console\Commands\Payments;

use App\ModelsExtended\ApplicationProduct;
use App\ModelsExtended\ApplicationProductPrice;
use App\Repositories\Stripe\StripeSubscriptionSDK;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Stripe\Price;

class FetchParameters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:fetch-params';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will fetch all parameters needed from stripe';

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
                $this->info( "\nFetching prices for " . $product->name );

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

            $stripeProduct = $sdk->getProductById( env( "STRIPE_LUCIA_PRODUCT_ID" ) );

            $product->stripe_product_id = $stripeProduct->id;
            $product->description = $stripeProduct->description??$product->description;
            $product->name = $stripeProduct->name;

            return $product->updateQuietly();

        }catch (\Exception $exception ){
            $this->error( "Error Updating/Creating Product" );
            Log::error( $exception->getMessage() , $exception->getTrace() );
        }

        return false;
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
            if( $stripePrice  )
            {
                $price->stripe_price_id = $stripePrice->id;
                $price->unit_amount = $stripePrice->unit_amount/100;
                $price->recurring = $stripePrice->recurring->interval;
                $price->stripe_price = $stripePrice->toArray();

                $price->updateQuietly();
            }
        }catch (\Exception $exception ){
            $this->error( "Error Updating/Creating Price" );
            Log::error( $exception->getMessage() , $exception->getTrace() );
        }
    }

    /**
     * @param ApplicationProductPrice $price
     * @param StripeSubscriptionSDK $sdk
     * @return Price|null
     * @throws ApiErrorException
     */
    private function getStripePrice(ApplicationProductPrice $price, StripeSubscriptionSDK $sdk): ?Price
    {
        switch ( $price->id )
        {
            case ApplicationProductPrice::LUCIA_EXPERIENCE_MONTHLY:
                return $sdk->getPriceById( env( 'STRIPE_LUCIA_MONTHLY_PRICE_ID' ) );

            case ApplicationProductPrice::LUCIA_EXPERIENCE_YEARLY:
                return $sdk->getPriceById( env( 'STRIPE_LUCIA_YEARLY_PRICE_ID' ) );

            case ApplicationProductPrice::LUCIA_COPILOT_ONLY_MONTHLY:
                return $sdk->getPriceById( env( 'STRIPE_LUCIA_COPILOT_ONLY_MONTHLY_PRICE_ID' ) );

            default:
                return null;
        }
    }
}
