<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

use App\Http\Middleware\IsCopilotMiddleware;

/*
|--------------------------------------------------------------------------
| Copilot Routes
|--------------------------------------------------------------------------
*/
$router->group([ 'prefix' => 'copilot/auth', 'namespace' => 'Copilot\Auth' ], function () use ($router) {
    $router->post('/register', 'AuthController@register');
    $router->post('/login', 'AuthController@login');
    $router->post('/password/reset', 'AuthController@forgotPassword');
    $router->post('/password/validate-reset-token', 'AuthController@validateResetToken');
    $router->post('/password/update', 'AuthController@updatePassword');

    $router->get('/stripe/onboarded', 'StripeAccountController@onboarded');

    $router->group([ 'middleware' => [ 'auth', IsCopilotMiddleware::class ] ], function () use ($router) {
        $router->post('/stripe/create', 'StripeAccountController@create');
        $router->post('/stripe/complete-account', 'StripeAccountController@completeAccountCreation');
        $router->get('/stripe/dashboard', 'StripeAccountController@expressDashboard');

        $router->post('/refresh', 'AuthController@refresh');
        $router->post('/logout', 'AuthController@logout');
    });
});

$router->group([ 'prefix' => 'copilot', 'namespace' => 'Copilot',
    'middleware' => [ 'auth', IsCopilotMiddleware::class ]
], function () use ($router) {

    $router->get('/profile', 'ProfileController@me');
    $router->post('/profile/update', 'ProfileController@update');
    $router->post('/profile/password/update', 'ProfileController@updatePassword');

    $router->group( [ 'prefix' => 'open-requests' ], function () use ($router) {
        $router->get('/', 'OpenRequestsController@fetchAll');
        $router->post('accept', 'OpenRequestsController@accept');
    });

    $router->group( [ 'prefix' => 'my-requests' ], function () use ($router) {
        $router->get('/', 'MyRequestsController@fetchAll');
        $router->get('/not-accepted', 'MyRequestsController@fetchAllNotAccepted');
        $router->get('/notifications', 'MyRequestsController@notifications');
        $router->get('/notifications/mark-as-read', 'MyRequestsController@markAsRead');

        $router->post('/archive', 'MyRequestsController@archive');
        $router->post('submitforapproval', 'MyRequestsController@submitForApproval');

        $router->group( [ 'prefix' => 'chat/{advisor_id}', 'namespace' => 'MyRequests'], function () use ($router) {
            $router->get('/', 'ChatController@listChats');
            $router->get('request', 'ChatController@fetch');
            $router->post('send-message', 'ChatController@sendChatMessage');
            $router->post('send-file', 'ChatController@sendFile');
            $router->post('mark-seen', 'ChatController@listChats');
            $router->post('mark-completed', 'ChatController@markAsCompleted');
            $router->post('mark-task-completed', 'ChatController@markTaskCompleted');
            $router->post('mark-task-uncompleted', 'ChatController@markTaskUncompleted');
//            $router->post('refund-request', 'ChatController@refundPayment');
            $router->post('return-request', 'ChatController@returnToPool');
        });
    });

    //Todo List
    $router->group( [ 'prefix' => 'todo' ], function () use ($router) {
        $router->post('/', 'TodoController@index');
        $router->post('/create', 'TodoController@create');
        $router->post('/update', 'TodoController@update');
        $router->post('/delete', 'TodoController@destroy');
        $router->post('/markasCompleted', 'TodoController@markAsCompleted');
    });

});

$router->group( [
    'prefix' => 'copilot/itineraries' ,
    'namespace' => 'Agent',
    'middleware' => [ 'auth', IsCopilotMiddleware::class ]
], function () use ($router) {
    $router->group( [ 'prefix' => '{itinerary_id}' ], function () use ($router) {
        $router->get('fetch', 'ItineraryController@fetch');
        $router->get('get-share-code', 'ItineraryController@getShareCode');

        $router->group( [ 'namespace' => 'Itinerary' ], function () use ($router) {

            require __DIR__.'/bookings.php';
        });

    });
});

$router->group( [
    'prefix' => 'copilot/suppliers' ,
    'namespace' => 'Copilot\Suppliers',
    'middleware' => [ 'auth', IsCopilotMiddleware::class ]
], function () use ($router) {
    $router->get('/google-place-hotel-search', 'LookupSuppliersController@googlePlaceIdHotelSearch');
    $router->get('/look-up', 'LookupSuppliersController@supplierLookup');
    $router->get('/', 'ListSuppliersController@fetchAll');
});

$router->group( [ 'prefix' => 'copilot/notes' ,
    'middleware' => [ 'auth', IsCopilotMiddleware::class ],
    'namespace' => 'Agent'
    ], function () use ($router) {
    $router->get('/look-up', 'NotesController@lookUp');
    $router->get('/auto-complete', 'NotesController@autoComplete');
});

$router->group( [ 'prefix' => 'copilot' ,
    'middleware' => [  ],
    'namespace' => 'Copilot',
    ], function () use ($router) {
    $router->get('/public/advisor-request/decline', 'RequestMailResponseController@declineRequest');
});
