<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Administrator Routes
|--------------------------------------------------------------------------
*/

use App\Http\Middleware\IsAdminMiddleware;
use App\Http\Middleware\IsAdminOrMasterAccountMiddleware;

$router->group([ 'prefix' => 'admin/auth', 'namespace' => 'Admin\Auth' ], function () use ($router) {
    $router->post('/login', 'AuthController@login');
    $router->post('/password/reset', 'AuthController@forgotPassword');
    $router->post('/password/validate-reset-token', 'AuthController@validateResetToken');
    $router->post('/password/update', 'AuthController@updatePassword');

    $router->group(['middleware' => 'auth'], function () use ($router) {
        $router->post('/refresh', 'AuthController@refresh');
        $router->post('/logout', 'AuthController@logout');
    });
});

$router->group(  [
    'prefix' => 'admin',
    'namespace' => 'Admin' ,
    'middleware' => [
        'auth', IsAdminOrMasterAccountMiddleware::class
    ]
], function () use ($router) {

    $router->get('/dashboard/fetch', [ 'middleware' => IsAdminMiddleware::class, 'uses' => 'DashboardController@fetch', ]);

    $router->group( [ 'prefix' => 'agents' , 'namespace' => 'Agents' ], function () use ($router) {

        $router->post('account/approve', 'AccountStatusController@approve');
        $router->post('account/reject', 'AccountStatusController@reject');
        $router->post('account/impersonate', 'AccountStatusController@impersonate');

        $router->post('account/reset-password', 'AccountStatusController@resetPassword');
        $router->delete('account/delete', 'AccountStatusController@deleteAccount');
        $router->post('account/create', 'AccountRegistrationController@register');

        $router->get('list', 'ListAgentsController@basicPagination');
        $router->get('list/grid', 'ListAgentsController@gridPagination');
        $router->get('partner/grid', 'PreferredPartnersController@gridPagination');
        $router->post('partner/create', 'PreferredPartnersController@store');
        $router->post('partner/update', 'PreferredPartnersController@updatePartner');
        $router->post('assignpartner', 'PreferredPartnersController@assignpartner');
        $router->post('removepartner', 'PreferredPartnersController@removepartner');
    });

    $router->post('/profile/update', 'ProfileController@update');
    $router->post('/profile/password/update', 'ProfileController@updatePassword');

    $router->group( [
        'prefix' => 'copilots' ,
        'namespace' => 'Copilots',
        'middleware' => [ IsAdminMiddleware::class]
    ], function () use ($router) {
        $router->get('/', 'CopilotAccountsController@fetchAll');
        $router->get('/archived_request/{copilot_id}','AdvisorRequestsController@list');
        $router->get('/advisor-tasks', 'AdvisorTasksController@fetchAll');
        $router->post('/advisor-tasks/{advisor_task_id}/update', 'AdvisorTasksController@update');
        $router->post('/advisor-tasks/add', 'AdvisorTasksController@store');

        $router->get('/advisor-discounts', 'AdvisorDiscountsController@fetchAll');
        $router->delete('/advisor-discounts/{advisor_discount_id}/delete', 'AdvisorDiscountsController@delete');
        $router->post('/advisor-discounts/{advisor_discount_id}/update', 'AdvisorDiscountsController@update');
        $router->post('/advisor-discounts/add', 'AdvisorDiscountsController@store');

        $router->get('/advisor-requests', 'AdvisorRequestsController@fetchAll');
        $router->post('/advisor-requests/{advisor_request_id}/make-available', 'AdvisorRequestsController@makeAvailable');
        $router->post('/advisor-requests/{advisor_request_id}/refund-request', 'AdvisorRequestsController@refundRequest');
        $router->get('/export-requests', 'AdvisorRequestsController@export');


        $router->get('account/fetch', 'AccountStatusController@fetch');
        $router->post('account/approve', 'AccountStatusController@approve');
        $router->post('account/reject', 'AccountStatusController@reject');

        $router->delete('account/delete', 'AccountStatusController@deleteAccount');
    });

    $router->group( [
        'prefix' => 'accounts' ,
        'middleware' => [ IsAdminMiddleware::class]
    ], function () use ($router) {
        $router->get('/', 'AccountsController@fetchAll');
    });

    $router->group( [
        'prefix' => 'clients' ,
        'namespace' => 'Clients',
        'middleware' => [ IsAdminMiddleware::class]
    ], function () use ($router) {
        $router->get('/', 'ClientAccountsController@fetchAll');
    });

    $router->get('/itineraries', 'ItinerariesController');
    $router->get('/calendar/events', 'CalendarController');

    $router->group( [
        'prefix' => 'providers' ,
        'namespace' => 'Providers',
        'middleware' => [ IsAdminMiddleware::class]
    ], function () use ($router) {

        $router->group( [
            'prefix' => 'suppliers' ,
        ], function () use ($router) {
            $router->get('/', 'SuppliersController@fetchAll');
            $router->post('add', 'SuppliersController@store');
            $router->post('import', 'SuppliersController@import');

            $router->group( [ 'prefix' => '{supplier_id}' ], function () use ($router) {
                $router->delete('delete', 'SuppliersController@delete');
                $router->post('update', 'SuppliersController@update');
                $router->get('fetch', 'SuppliersController@fetch');
            });
        });
        $router->get('cruise-lines', 'CruiseLinesController@fetchAll');
    });

    $router->group( [
        'prefix' => 'registration-access-codes' ,
        'middleware' => [ IsAdminMiddleware::class]
    ], function () use ($router) {

        $router->get('/', 'RegistrationAccessCodesController@fetchAll');
        $router->post('add', 'RegistrationAccessCodesController@store');

        $router->group( [ 'prefix' => '{access_code_id}' ], function () use ($router) {
            $router->delete('delete', 'RegistrationAccessCodesController@delete');
            $router->get('fetch', 'RegistrationAccessCodesController@fetch');
            $router->post('mail', 'RegistrationAccessCodesController@mail');
        });
    });

    $router->group( [
        'prefix' => 'manage-administrators',
        'middleware' => [ IsAdminMiddleware::class]
    ], function () use ($router) {
        $router->get('/', 'ManageAdministratorsController@fetchAll');
        $router->post('add', 'ManageAdministratorsController@store');

        $router->group( [ 'prefix' => '{admin_user_id}' ], function () use ($router) {
            $router->delete('delete', 'ManageAdministratorsController@delete');
//            $router->post('update', 'ManageAdministratorsController@update');
            $router->get('fetch', 'ManageAdministratorsController@fetch');
        });
    });

    $router->group( [
        'prefix' => 'manage-master-accounts',
        'middleware' => [ IsAdminOrMasterAccountMiddleware::class]
    ], function () use ($router) {
        $router->get('/', 'ManageMasterAccountsController@fetchAll');
        $router->post('add', 'ManageMasterAccountsController@store');

        $router->group( [ 'prefix' => '{master_user_id}' ], function () use ($router) {
            $router->delete('delete', 'ManageMasterAccountsController@delete');
            $router->get('fetch', 'ManageMasterAccountsController@fetch');
        });
    });

});
