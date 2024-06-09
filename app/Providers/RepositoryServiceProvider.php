<?php

namespace App\Providers;

use App\Repositories\AmadeusFlightAPI\FlightSearchAPI;
use App\Repositories\IFlightSearchAPI;
use App\Repositories\SMS\AwsSNSTextMessage;
use App\Repositories\SMS\ISMSSender;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        // You can change which flight search api to use here
        $this->app->bind(IFlightSearchAPI::class, FlightSearchAPI::class);
        $this->app->bind(ISMSSender::class, AwsSNSTextMessage::class);
    }
}
