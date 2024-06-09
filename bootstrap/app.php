<?php

use App\Providers\BroadcastServiceProvider;
use App\Providers\RepositoryServiceProvider;
use Fruitcake\Cors\HandleCors;
use Illuminate\Support\Str;

require_once __DIR__.'/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

//$app->instance('path.config', app()->basePath() . DIRECTORY_SEPARATOR . 'config');
//$app->instance('path.storage', app()->basePath() . DIRECTORY_SEPARATOR . 'storage');

 $app->withFacades();

 $app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

/*
|--------------------------------------------------------------------------
| Register Config Files
|--------------------------------------------------------------------------
|
| Now we will register the "app" configuration file. If the file exists in
| your configuration directory it will be loaded; otherwise, we'll load
| the default version. You may register other files below as needed.
|
*/
$app->configure('cors');
$app->configure('broadcasting');

$app->configure('mail');
$app->configure('tinker');
$app->alias('mailer', Illuminate\Mail\Mailer::class);
$app->alias('mailer', Illuminate\Contracts\Mail\Mailer::class);
$app->alias('mailer', Illuminate\Contracts\Mail\MailQueue::class);
$app->alias('mail.manager', Illuminate\Mail\MailManager::class);
$app->alias('mail.manager', Illuminate\Contracts\Mail\Factory::class);

$app->alias('DataTables', Yajra\DataTables\Facades\DataTables::class);
$app->alias('Zoom', MacsiDigital\Zoom\Facades\Zoom::class);
$app->alias('Excel' , Maatwebsite\Excel\Facades\Excel::class);
$app->configure('zoom');

if( $app->environment() === 'development' )
{
    // config files not loading automatically
    $app->configure('models');
}

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

 $app->middleware([
     HandleCors::class,
//     \App\Http\Middleware\ExampleMiddleware::class,
 ]);

 $app->routeMiddleware([
     'auth' => App\Http\Middleware\Authenticate::class,
 ]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

$app->register(App\Providers\AppServiceProvider::class);
$app->register(App\Providers\EventServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class);
$app->register(RepositoryServiceProvider::class );
$app->register(Fruitcake\Cors\CorsServiceProvider::class);
$app->register(Tymon\JWTAuth\Providers\LumenServiceProvider::class);
$app->register(Illuminate\Mail\MailServiceProvider::class);
$app->register(\Illuminate\Notifications\NotificationServiceProvider::class);
$app->register(Yajra\DataTables\DataTablesServiceProvider::class );
$app->register(Illuminate\Redis\RedisServiceProvider::class);
$app->register(BroadcastServiceProvider::class);
$app->register(Irazasyed\Larasupport\Providers\ArtisanServiceProvider::class);
$app->register(MacsiDigital\Zoom\Providers\ZoomServiceProvider::class);
$app->register(\Laravel\Tinker\TinkerServiceProvider::class);
$app->register(\Spatie\SlackAlerts\SlackAlertsServiceProvider::class);
$app->register(Maatwebsite\Excel\ExcelServiceProvider::class);





if( $app->environment() === 'development' )
{
    $app->register(Flipbox\LumenGenerator\LumenGeneratorServiceProvider::class);
    $app->register(\KitLoong\MigrationsGenerator\MigrationsGeneratorServiceProvider::class);
    $app->register(\Reliese\Coders\CodersServiceProvider::class );
}

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {

    require __DIR__.'/../routes/web.php';

    if( array_key_exists( 'REQUEST_URI', $_SERVER ) )
    {
        $url_base_path =  Str::of( "$_SERVER[REQUEST_URI]" );

        if( $url_base_path->startsWith("/agent") )
            require __DIR__.'/../routes/agent.php';

        if( $url_base_path->startsWith("/admin") )
            require __DIR__.'/../routes/admin.php';

        if( $url_base_path->startsWith("/copilot") )
            require __DIR__.'/../routes/copilot.php';

        if( $url_base_path->startsWith("/client") )
            require __DIR__.'/../routes/client.php';
    }else{

        // add all if this call is from CLI
        require __DIR__.'/../routes/agent.php';
        require __DIR__.'/../routes/admin.php';
        require __DIR__.'/../routes/copilot.php';
        require __DIR__.'/../routes/client.php';
    }


});

return $app;
