<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Client Routes
|--------------------------------------------------------------------------
*/

use App\Http\Middleware\IsClientMiddleware;

$router->group([ 'prefix' => 'client/auth', 'namespace' => 'Client\Auth' ], function () use ($router) {
    $router->post('/register', 'AuthController@register');
    $router->post('/login', 'AuthController@login');
    $router->post('/password/reset', 'AuthController@forgotPassword');
    $router->post('/password/validate-reset-token', 'AuthController@validateResetToken');
    $router->post('/password/update', 'AuthController@updatePassword');

    $router->group(['middleware' => 'auth'], function () use ($router) {
        $router->post('/refresh', 'AuthController@refresh');
        $router->post('/logout', 'AuthController@logout');
    });
});

$router->group([ 'prefix' => 'client', 'namespace' => 'Client',
    'middleware' => [ 'auth', IsClientMiddleware::class
    ] ], function () use ($router) {

    $router->get('/profile', 'ProfileController@me');
    $router->post('/profile/delete', 'ProfileController@deleteMe');
    $router->post('/profile/update', 'ProfileController@update');
    $router->post('/profile/send-validation-token', 'ProfileController@createResetToken');
    $router->post('/profile/update-email', 'ProfileController@updateEmail');
    $router->post('/profile/update-phone', 'ProfileController@updatePhone');

    $router->get('/itineraries', 'ItinerariesController@fetchAll');
    $router->get('/itineraries/{itinerary_id}/fetch', 'ItinerariesController@fetch');

    $router->get('/calendar/events', 'Calendar\EventsController@index');
    $router->get('/calendar/events/detail', 'Calendar\EventsController@detail');

});

