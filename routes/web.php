<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Router;

$router->post( 'meetings/zoom/webhook', function(Request $request){
    \Log::info($request);
});
$router->post( 'stripe/webhook',"webhookController@stripeSubscriptionUpdate");
// $router->post( 'stripe/webhook',function (Request $request){
//     \Log::info($request['data']['object']);
// });
$router->group(['middleware' => 'auth'], function (Router $router)  {
    $router->post('broadcasting/auth', ['uses' => 'BroadcastController@authenticate']);
});

$router->group(['middleware' => 'auth'], function (Router $router)  {
    $router->get('/pusher/beams-auth', 'PusherBeamController@authenticate');
});



$router->post('zoom/test', ['uses' => 'MeetingController@store']);

$router->get('/', function () use ($router) {
    return redirect()->to( env( 'DOCUMENTATION_URL' ) );
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATION Routes
|--------------------------------------------------------------------------
*/
$router->group([ 'prefix' => 'auth', 'namespace' => 'Auth' ], function () use ($router) {
    $router->post('/check-email-availability', 'AuthController@emailAvailability');
    $router->post('/register', 'AuthController@register');
    $router->post('/login', 'AuthController@login');
    $router->post('/password/reset', 'AuthController@forgotPassword');
    $router->post('/password/validate-reset-token', 'AuthController@validateResetToken');
    $router->post('/password/update', 'AuthController@updatePassword');

    $router->post('/registration-access-code/validate', 'AuthController@validateRegistrationToken');

    $router->post('/impersonate/{impersonation_token}', 'AuthController@impersonate');

    $router->get('/google-calendar/oauth/callback', 'GoogleOAuthController@completeCalendarOAuth');

    $router->group(['middleware' => 'auth'], function () use ($router) {

        $router->post('/google-calendar/oauth', 'GoogleOAuthController@beginCalendarOAuth');
        $router->delete('/google-calendar/oauth/revoke', 'GoogleOAuthController@revokeCalendarOAuth');

        $router->post('/refresh', 'AuthController@refresh');
        $router->post('/logout', 'AuthController@logout');

    });
});

/*
***************************************************************************
Categories Listing
***************************************************************************
*/
$router->group( [ 'prefix' => 'categories'], function () use ($router) {
    $router->get('list', 'Category\CategoryController@showAll');
});

$router->group( [ 'prefix' => 'partners', 'namespace' => 'Agent\AdvisorRequests'], function () use ($router) {
    $router->get('list', 'AdvisorController@fetchPartners');
});

/*
|--------------------------------------------------------------------------
| Constants Routes
|--------------------------------------------------------------------------
*/
$router->group([ 'prefix' => 'constants', 'namespace' => 'Constants' ], function () use ($router) {
    $router->get('/avatars', 'ConstantController@avatars');
    $router->get('/account-status', 'ConstantController@accountStatus');
    $router->get('/advisor-request-type', 'ConstantController@advisorRequestType');
    $router->get('/agency-usage-mode', 'ConstantController@agencyUsageMode');
    $router->get('/airports', 'ConstantController@airports');
    $router->get('/airlines', 'ConstantController@airlines');
    $router->get('/amenities', 'ConstantController@amenities');
    $router->get('/bedding-types', 'ConstantController@beddingTypes');
    $router->get('/booking-category', 'ConstantController@bookingCategory');
    $router->get('/countries', 'ConstantController@countries');
    $router->get('/currency-types', 'ConstantController@currencyTypes');
    $router->get('/feedback-topics', 'ConstantController@feedbackTopics');
    $router->get('/itinerary-status', 'ConstantController@itineraryStatus');
    $router->get('/passenger-type', 'ConstantController@passengerType');
    $router->get('/priority', 'ConstantController@priority');
    $router->get('/roles', 'ConstantController@roles');
    $router->get('/timezones', 'ConstantController@timezones');
    $router->get('/transit-type', 'ConstantController@transitType');
    $router->get('/property-position', 'ConstantController@propertyPosition');
    $router->get('/property-design', 'ConstantController@propertyDesign');
    $router->get('/subscription-prices', 'ConstantController@subscriptionPrices');
    $router->get('/subscription-prices/{partner_id}', 'ConstantController@partnerSubscriptionPrices');

});

/*
|--------------------------------------------------------------------------
| Shares Routes
|--------------------------------------------------------------------------
*/
$router->group([ 'prefix' => 'shares', 'namespace' => 'Shares' ], function () use ($router) {
    $router->get('/itineraries/{share_itinerary_key}', 'ItinerarySharedController');
});

/*
|--------------------------------------------------------------------------
| TEST Routes
|--------------------------------------------------------------------------
*/
$router->get( 'test', 'TestController@index');
$router->get( 'test2', 'TestController@index2');
$router->get( 'external/lucia-advisor/nda', function (){
    return file_get_contents( 'https://www.letslucia.com/agreement-advisors');
});
$router->get( 'beam', 'BeamPermissionController@index');
$router->get( 'beam-prompt', 'BeamPermissionController@prompt');

