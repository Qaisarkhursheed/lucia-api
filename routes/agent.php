<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use App\Http\Middleware\IsLicensedMiddleware;
use App\Http\Middleware\IsLuciaExperiencedAgentMiddleware;
use App\Http\Middleware\IsTravelAgentMiddleware;


/*
|--------------------------------------------------------------------------
| AGENTS Routes
|--------------------------------------------------------------------------
*/
$router->group(  [
    'prefix' => 'agent',
    'namespace' => 'Agent' ,
], function () use ($router) {
    $router->post('advisor-requests/advisor/{advisor_id}/submit-feedback', 'AdvisorRequests\FeedbackController@submitFeedback');
    $router->group( [ 'prefix' => 'payment-methods'  ], function () use ($router) {
        $router->post('/generate-token', 'PaymentMethodsController@generateCardToken');
    });
});

$router->group(  [
    'prefix' => 'agent',
    'namespace' => 'Agent' ,
    'middleware' => [
        'auth', IsTravelAgentMiddleware::class
    ]
], function () use ($router) {

    $router->group( [ 'prefix' => 'license'  ], function () use ($router) {
        $router->get('/refresh-subscription', 'LicenseController@refreshSubscription');
        $router->post('/subscribe', 'LicenseController@subscribe');
        $router->get('/checkout-successful', 'LicenseController@checkoutSuccessful');
//        $router->post('/checkout', 'LicenseController@checkout');
        $router->post('/billing-portal', 'LicenseController@billingPortal');
        $router->post('/subscribe-with-card', 'LicenseController@subscribeWithCard');
        $router->post('/invoices/pay', 'LicenseController@payPendingInvoice');
        $router->get('/invoices/fetch-all', 'LicenseController@fetchAllInvoices');
        $router->get('/invoices/fetch-pending', 'LicenseController@fetchPendingInvoices');
        $router->delete('/cancel-subscription', 'LicenseController@cancelSubscription');
        $router->get('/renew-subscription', 'LicenseController@renewSubscription');
        $router->post('/new-subscription', 'LicenseController@newSubscription');
    });

    $router->group( [ 'prefix' => 'partners', 'namespace' => 'AdvisorRequests'], function () use ($router) {
        $router->get('list', 'AdvisorController@fetchPartners');
    });

    $router->group( [ 'prefix' => 'payment-methods'  ], function () use ($router) {
        $router->post('/add', 'PaymentMethodsController@addPaymentMethod');
//        $router->post('/generate-token', 'PaymentMethodsController@generateCardToken');
//        $router->post('/change-default-payment-method', 'PaymentMethodsController@changeDefaultPaymentMethod');
        $router->delete('/delete', 'PaymentMethodsController@deletePaymentMethod');
        $router->get('/fetch', 'PaymentMethodsController@listAll');
    });

    $router->group( [
        'prefix' => 'itineraries' ,
        'middleware' => [ IsLicensedMiddleware::class, IsLuciaExperiencedAgentMiddleware::class ]
    ], function () use ($router) {
        $router->get('/', 'ItineraryController@fetchAll');
        $router->post('/add', 'ItineraryController@store');

        $router->group( [ 'prefix' => '{itinerary_id}' ], function () use ($router) {
            $router->delete('delete', 'ItineraryController@delete');
            $router->post('update', 'ItineraryController@update');
            $router->post('update/abstract', 'ItineraryController@updateAbstract');
            $router->post('update/logo', 'ItineraryController@updateLogo');
//            $router->post('update/booking-date', 'ItineraryController@updateBookingDate');
            $router->post('set-status', 'ItineraryController@setStatus');
            $router->get('fetch', 'ItineraryController@fetch');
            $router->get('get-share-code', 'ItineraryController@getShareCode');
            $router->delete('delete-share-code', 'ItineraryController@deleteShareCode');
            $router->post('send-invitation', 'ItineraryController@sendSharedInvitation');
            $router->post('send-invitation-to-client', 'ItineraryController@sendInvitationToClient');
            $router->post('clone-itinerary', 'ItineraryController@cloneItinerary');

            $router->group( [ 'namespace' => 'Itinerary' ], function () use ($router) {

                $router->group( [ 'prefix' => 'passengers' ], function () use ($router) {
                    $router->get('/', 'PassengerController@fetchAll');
                    $router->post('add', 'PassengerController@store');

                    $router->group( [ 'prefix' => '{passenger_id}' ], function () use ($router) {
                        $router->delete('delete', 'PassengerController@delete');
                        $router->post('update', 'PassengerController@update');
                        $router->get('fetch', 'PassengerController@fetch');
                    });
                });

                $router->group( [ 'prefix' => 'pictures' ], function () use ($router) {
                    $router->get('/', 'PictureController@fetchAll');
                    $router->post('add', 'PictureController@store');

                    $router->group( [ 'prefix' => '{picture_id}' ], function () use ($router) {
                        $router->delete('delete', 'PictureController@delete');
                        $router->get('fetch', 'PictureController@fetch');
                    });
                });

                $router->group( [ 'prefix' => 'documents' ], function () use ($router) {
                    $router->get('/', 'DocumentController@fetchAll');
                    $router->post('add', 'DocumentController@store');

                    $router->group( [ 'prefix' => '{document_id}' ], function () use ($router) {
                        $router->delete('delete', 'DocumentController@delete');
                        $router->get('fetch', 'DocumentController@fetch');
                    });
                });

                $router->group( [ 'prefix' => 'tasks' ], function () use ($router) {
                    $router->get('/', 'TasksController@fetchAll');
                    $router->post('add', 'TasksController@store');

                    $router->group( [ 'prefix' => '{task_id}' ], function () use ($router) {
                        $router->delete('delete', 'TasksController@delete');
                        $router->get('fetch', 'TasksController@fetch');
                        $router->post('mark-completed', 'TasksController@markCompleted');
                    });
                });

                $router->group( [ 'prefix' => 'client-emails' ], function () use ($router) {
                    $router->get('/', 'ClientEmailController@fetchAll');
                    $router->post('add', 'ClientEmailController@store');

                    $router->group( [ 'prefix' => '{client_email_id}' ], function () use ($router) {
                        $router->post('update', 'ClientEmailController@update');
                        $router->delete('delete', 'ClientEmailController@delete');
                        $router->get('fetch', 'ClientEmailController@fetch');
                    });
                });

                $router->group( [ 'prefix' => 'ocr' ], function () use ($router) {
                    $router->post('import', 'ImportBookingOcrController@store');

                    $router->group( [ 'prefix' => 'status/{import_ocr_id}' ], function () use ($router) {
                        $router->get('/', 'ImportBookingOcrController@status');
                    });
                });

                require __DIR__.'/bookings.php';
            });

        });
    });

    $router->group( [ 'prefix' => 'calendar' , 'namespace' => 'Calendar' , 'middleware' => [ IsLicensedMiddleware::class, IsLuciaExperiencedAgentMiddleware::class ]  ], function () use ($router) {
        $router->get('/events', 'EventsController');
    });

    $router->group( [ 'prefix' => 'suppliers' , 'namespace' => 'Suppliers' , 'middleware' => [ IsLicensedMiddleware::class, IsLuciaExperiencedAgentMiddleware::class ]  ], function () use ($router) {
        $router->get('/google-place-hotel-search', 'LookupSuppliersController@googlePlaceIdHotelSearch');
        $router->get('/look-up', 'LookupSuppliersController@supplierLookup');
        $router->get('/look-up-ships', 'LookupSuppliersController@shipLookup');
        $router->get('/', 'ListSuppliersController@fetchAll');
        $router->post('add', 'ListSuppliersController@store');

        $router->group( [ 'prefix' => '{supplier_id}' ], function () use ($router) {

            $router->post('pictures/add', 'SupplierPictureController@store');
            $router->delete('pictures/{supplier_picture_id}/delete', 'SupplierPictureController@delete');
            $router->get('pictures', 'SupplierPictureController@fetchAll');

            $router->delete('delete', 'ListSuppliersController@delete');
            $router->post('update', 'ListSuppliersController@update');
            $router->get('fetch', 'ListSuppliersController@fetch');
        });
    });

    $router->group( [ 'prefix' => 'advisor', 'namespace' => 'AdvisorRequests' ], function () use ($router) {
        $router->get('/get-copilots', 'AdvisorController@getCopilots');
        $router->get('/mark-as-un-favorite/{id}', 'AdvisorController@markAsUnFavorite');
        $router->get('/mark-as-favorite/{copilot_id}', 'AdvisorController@markAsFavorite');


    });

    $router->group( [ 'prefix' => 'advisor-requests' , 'middleware' => [  ] ], function () use ($router) {
        $router->get('/', 'AdvisorRequestsController@quickList');
        $router->get('/concierges', 'AdvisorRequestsController@concierges');
        $router->get('/top-concierges', 'AdvisorRequestsController@topConcierges');
        $router->get('/recent-concierges', 'AdvisorRequestsController@recentConcierges');
        $router->get('/recent-requests', 'AdvisorRequestsController@recentRequests');
        $router->get('/notifications', 'AdvisorRequestsController@notifications');
        $router->get('/notifications/mark-as-read', 'AdvisorRequestsController@markAsRead');
        $router->get('unreadmessages', 'AdvisorRequestsController@unReadMessages');

        $router->group( [ 'prefix' => 'advisor', 'namespace' => 'AdvisorRequests' ], function () use ($router) {
            $router->get('requests', 'AdvisorController@fetchAll');
            $router->get('getAllrequests', 'AdvisorController@fetchAllRequests');
            $router->get('chat-requests', 'AdvisorController@getChatWiseRequest');
            $router->post('hire', 'AdvisorController@store');
            $router->post('hire/assign-new-copilot', 'AdvisorController@assignNewCopilot');
            $router->post('hire/open-to-all', 'AdvisorController@updateStatusOpenToAll');
            $router->post('hire/cancel-advisor-request', 'AdvisorController@cancelAdvisorRequest');
            $router->get('saved-hours', 'AdvisorController@savedHours');
            $router->get('unread-messages-count', 'AdvisorController@getunreadmessagescount');
            $router->group( [ 'prefix' => '{advisor_id}' ], function () use ($router) {
                $router->delete('delete', 'AdvisorController@delete');
                $router->post('apply-discount', 'AdvisorController@applyDiscount');
                $router->post('remove-discount', 'AdvisorController@removeDiscount');
                $router->get('fetch', 'AdvisorController@fetch');
//                $router->post('pay', 'AdvisorController@pay');

                $router->post('pay-using-stored-payment', 'AdvisorController@payUsingStoredPayment');
                $router->post('pay-using-intent', 'AdvisorController@payUsingIntent');
                $router->post('complete-intent-payment', 'AdvisorController@completeIntentPayment');

            });
        });
        // $router->get('chat', 'AdvisorController@listChats');
        // $router->get('chat/{advisor_id}', 'ChatController@listChats');
        $router->group( [ 'prefix' => 'chat/{advisor_id}', 'namespace' => 'AdvisorRequests'], function () use ($router) {
            $router->get('/', 'ChatController@listChats');
            $router->get('request', 'ChatController@fetch');
            $router->post('extend-deadline', 'ChatController@extendDeadline');
            $router->post('send-message', 'ChatController@sendChatMessage');
            $router->post('send-file', 'ChatController@sendFile');
            $router->post('mark-seen', 'ChatController@listChats');
            $router->post('mark-completed', 'ChatController@markAsCompleted');
            $router->post('createZoomMeeting', 'ChatController@createZoomMeeting');
            $router->post('declineZoomMeeting', 'ChatController@declineZoomMeeting');

        });

        $router->group( [ 'prefix' => 'chat', 'namespace' => 'AdvisorRequests'], function () use ($router) {
            $router->get('/', 'ChatController@listChats');
            // $router->get('request', 'ChatController@fetch');
        });
    });

    $router->group( [ 'prefix' => 'travellers' , 'middleware' => [ IsLicensedMiddleware::class, IsLuciaExperiencedAgentMiddleware::class ]  ], function () use ($router) {
        $router->get('/', 'TravellersController@fetchAll');
        $router->post('add', 'TravellersController@store');

        $router->group( [ 'prefix' => '{traveller_id}' ], function () use ($router) {
            $router->get('/itineraries', 'TravellersController@showItineraries');
            $router->delete('delete', 'TravellersController@delete');
            $router->post('update', 'TravellersController@update');
            $router->get('fetch', 'TravellersController@fetch');

            $router->group( [ 'prefix' => 'documents' , 'namespace' => 'Travellers'], function () use ($router) {
                $router->get('/', 'SupportDocumentController@fetchAll');
                $router->post('add', 'SupportDocumentController@store');

                $router->group( [ 'prefix' => '{support_document_id}' ], function () use ($router) {
                    $router->delete('delete', 'SupportDocumentController@delete');
                    $router->get('fetch', 'SupportDocumentController@fetch');
                });
            });

        });
    });

    $router->group( [ 'prefix' => 'notes' , 'middleware' => [ IsLicensedMiddleware::class, IsLuciaExperiencedAgentMiddleware::class ]  ], function () use ($router) {
        $router->get('/', 'NotesController@fetchAll');
        $router->get('/look-up', 'NotesController@lookUp');
        $router->get('/auto-complete', 'NotesController@autoComplete');
        $router->post('add', 'NotesController@store');

        $router->group( [ 'prefix' => '{note_id}' ], function () use ($router) {
            $router->delete('delete', 'NotesController@delete');
            $router->post('update', 'NotesController@update');
            $router->get('fetch', 'NotesController@fetch');
        });
    });

    $router->get('/global-search', 'GlobalSearchController');

    $router->get('/profile', 'ProfileController@me');
    $router->post('/profile/update', 'ProfileController@update');
    $router->post('/profile/update-itinerary-design', 'ProfileController@updateItineraryDesign');
    $router->post('/profile/password/update', 'ProfileController@updatePassword');
    $router->get('/invoiceList', 'ProfileController@invoiceList');
});
