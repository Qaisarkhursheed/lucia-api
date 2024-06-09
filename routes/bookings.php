<?php
/** @var \Laravel\Lumen\Routing\Router $router */

$router->group( [ 'prefix' => 'bookings', 'namespace' => 'Bookings' ], function () use ($router) {

    $router->post('duplicate', 'BookingsController@duplicate');
    $router->post('shift', 'BookingsController@shiftBooking');

    $router->group( [ 'prefix' => 'hotels' ], function () use ($router) {
        $router->get('/', 'HotelController@fetchAll');
        $router->post('add', 'HotelController@store');

        $router->group( [ 'prefix' => '{hotel_id}' ], function () use ($router) {
            $router->post('update', 'HotelController@update');
            $router->delete('delete', 'HotelController@delete');
            $router->get('fetch', 'HotelController@fetch');

            $router->group( [ 'namespace' => 'Hotels' ], function () use ($router) {
                $router->group( [ 'prefix' => 'passengers' ], function () use ($router) {
                    $router->get('/', 'PassengerController@fetchAll');
                    $router->post('add', 'PassengerController@store');

                    $router->group( [ 'prefix' => '{hotel_passenger_id}' ], function () use ($router) {
                        $router->delete('delete', 'PassengerController@delete');
                        $router->post('update', 'PassengerController@update');
                        $router->get('fetch', 'PassengerController@fetch');
                    });
                });

                $router->group( [ 'prefix' => 'amenities' ], function () use ($router) {
                    $router->get('/', 'AmenitiesController@fetchAll');
                    $router->post('add', 'AmenitiesController@store');

                    $router->group( [ 'prefix' => '{hotel_amenity_id}' ], function () use ($router) {
                        $router->delete('delete', 'AmenitiesController@delete');
                        $router->post('update', 'AmenitiesController@update');
                        $router->get('fetch', 'AmenitiesController@fetch');
                    });
                });

                $router->group( [ 'prefix' => 'rooms' ], function () use ($router) {
                    $router->get('/', 'RoomsController@fetchAll');
                    $router->post('add', 'RoomsController@store');

                    $router->group( [ 'prefix' => '{hotel_room_id}' ], function () use ($router) {
                        $router->delete('delete', 'RoomsController@delete');
                        $router->delete('delete-image', 'RoomsController@deleteImage');
                        $router->post('add-image', 'RoomsController@addImage');
                        $router->post('update', 'RoomsController@update');
                        $router->get('fetch', 'RoomsController@fetch');
                    });
                });

                $router->group( [ 'prefix' => 'pictures' ], function () use ($router) {
                    $router->get('/', 'PictureController@fetchAll');
                    $router->post('add', 'PictureController@store');
                    $router->delete('{hotel_picture_id}/delete', 'PictureController@delete');
                });
            });


        });
    });

    $router->group( [ 'prefix' => 'flights' ], function () use ($router) {
        $router->get('/', 'FlightController@fetchAll');
        $router->get('/search-flight-number', 'FlightController@searchFlightNumber');
        $router->post('add', 'FlightController@store');

        $router->group( [ 'prefix' => '{flight_id}' ], function () use ($router) {
            $router->post('update', 'FlightController@update');
            $router->delete('delete', 'FlightController@delete');
            $router->get('fetch', 'FlightController@fetch');

            $router->group( [ 'namespace' => 'Flights' ], function () use ($router) {
                $router->group( [ 'prefix' => 'passengers' ], function () use ($router) {
                    $router->get('/', 'PassengerController@fetchAll');
                    $router->post('add', 'PassengerController@store');

                    $router->group( [ 'prefix' => '{flight_passenger_id}' ], function () use ($router) {
                        $router->delete('delete', 'PassengerController@delete');
                        $router->post('update', 'PassengerController@update');
                        $router->get('fetch', 'PassengerController@fetch');
                    });
                });

                $router->group( [ 'prefix' => 'segments' ], function () use ($router) {
                    $router->get('/', 'FlightSegmentController@fetchAll');
                    $router->post('add', 'FlightSegmentController@store');

                    $router->group( [ 'prefix' => '{flight_segment_id}' ], function () use ($router) {
                        $router->delete('delete', 'FlightSegmentController@delete');
                        $router->post('update', 'FlightSegmentController@update');
                        $router->get('fetch', 'FlightSegmentController@fetch');
                    });
                });

                $router->group( [ 'prefix' => 'pictures' ], function () use ($router) {
                    $router->get('/', 'PictureController@fetchAll');
                    $router->post('add', 'PictureController@store');
                    $router->delete('{flight_picture_id}/delete', 'PictureController@delete');
                });
            });
        });
    });

    $router->group( [ 'prefix' => 'cruises' ], function () use ($router) {
        $router->get('/', 'CruiseController@fetchAll');
        $router->post('add', 'CruiseController@store');

        $router->group( [ 'prefix' => '{cruise_id}' ], function () use ($router) {
            $router->post('update', 'CruiseController@update');
            $router->delete('delete', 'CruiseController@delete');
            $router->get('fetch', 'CruiseController@fetch');

            $router->group( [ 'namespace' => 'Cruises' ], function () use ($router) {
                $router->group( [ 'prefix' => 'passengers' ], function () use ($router) {
                    $router->get('/', 'PassengerController@fetchAll');
                    $router->post('add', 'PassengerController@store');

                    $router->group( [ 'prefix' => '{cruise_passenger_id}' ], function () use ($router) {
                        $router->delete('delete', 'PassengerController@delete');
                        $router->post('update', 'PassengerController@update');
                        $router->get('fetch', 'PassengerController@fetch');
                    });
                });

                $router->group( [ 'prefix' => 'cabins' ], function () use ($router) {
                    $router->get('/', 'CabinsController@fetchAll');
                    $router->post('add', 'CabinsController@store');

                    $router->group( [ 'prefix' => '{cruise_cabin_id}' ], function () use ($router) {
                        $router->delete('delete', 'CabinsController@delete');
                        $router->post('update', 'CabinsController@update');
                        $router->get('fetch', 'CabinsController@fetch');
                    });
                });

                $router->group( [ 'prefix' => 'pictures' ], function () use ($router) {
                    $router->get('/', 'PictureController@fetchAll');
                    $router->post('add', 'PictureController@store');
                    $router->delete('{cruise_picture_id}/delete', 'PictureController@delete');
                });
            });


        });
    });

    $router->group( [ 'prefix' => 'transports' ], function () use ($router) {
        $router->get('/', 'TransportController@fetchAll');
        $router->post('add', 'TransportController@store');

        $router->group( [ 'prefix' => '{transport_id}' ], function () use ($router) {
            $router->post('update', 'TransportController@update');
            $router->delete('delete', 'TransportController@delete');
            $router->get('fetch', 'TransportController@fetch');

            $router->group( [ 'namespace' => 'Transports' ], function () use ($router) {
                $router->group( [ 'prefix' => 'passengers' ], function () use ($router) {
                    $router->get('/', 'PassengerController@fetchAll');
                    $router->post('add', 'PassengerController@store');

                    $router->group( [ 'prefix' => '{transport_passenger_id}' ], function () use ($router) {
                        $router->delete('delete', 'PassengerController@delete');
                        $router->post('update', 'PassengerController@update');
                        $router->get('fetch', 'PassengerController@fetch');
                    });
                });

                $router->group( [ 'prefix' => 'pictures' ], function () use ($router) {
                    $router->get('/', 'PictureController@fetchAll');
                    $router->post('add', 'PictureController@store');
                    $router->delete('{transport_picture_id}/delete', 'PictureController@delete');
                });
            });


        });
    });

    $router->group( [ 'prefix' => 'concierges' ], function () use ($router) {
        $router->get('/', 'ConciergeController@fetchAll');
        $router->post('add', 'ConciergeController@store');

        $router->group( [ 'prefix' => '{concierge_id}' ], function () use ($router) {
            $router->post('update', 'ConciergeController@update');
            $router->delete('delete', 'ConciergeController@delete');
            $router->get('fetch', 'ConciergeController@fetch');

            $router->group( [ 'namespace' => 'Concierges' ], function () use ($router) {

                $router->group( [ 'prefix' => 'pictures' ], function () use ($router) {
                    $router->get('/', 'PictureController@fetchAll');
                    $router->post('add', 'PictureController@store');
                    $router->delete('{concierge_picture_id}/delete', 'PictureController@delete');
                });
            });


        });
    });

    $router->group( [ 'prefix' => 'tours' ], function () use ($router) {
        $router->get('/', 'TourController@fetchAll');
        $router->post('add', 'TourController@store');

        $router->group( [ 'prefix' => '{tour_id}' ], function () use ($router) {
            $router->post('update', 'TourController@update');
            $router->delete('delete', 'TourController@delete');
            $router->get('fetch', 'TourController@fetch');

            $router->group( [ 'namespace' => 'Tours' ], function () use ($router) {

                $router->group( [ 'prefix' => 'pictures' ], function () use ($router) {
                    $router->get('/', 'PictureController@fetchAll');
                    $router->post('add', 'PictureController@store');
                    $router->delete('{tour_picture_id}/delete', 'PictureController@delete');
                });
            });
        });
    });

    $router->group( [ 'prefix' => 'insurances' ], function () use ($router) {
        $router->get('/', 'InsuranceController@fetchAll');
        $router->post('add', 'InsuranceController@store');

        $router->group( [ 'prefix' => '{insurance_id}' ], function () use ($router) {
            $router->post('update', 'InsuranceController@update');
            $router->delete('delete', 'InsuranceController@delete');
            $router->get('fetch', 'InsuranceController@fetch');

            $router->group( [ 'namespace' => 'Insurances' ], function () use ($router) {

                $router->group( [ 'prefix' => 'pictures' ], function () use ($router) {
                    $router->get('/', 'PictureController@fetchAll');
                    $router->post('add', 'PictureController@store');
                    $router->delete('{insurance_picture_id}/delete', 'PictureController@delete');
                });
            });
        });
    });

    $router->group( [ 'prefix' => 'others' ], function () use ($router) {
        $router->get('/', 'OtherController@fetchAll');
        $router->post('add', 'OtherController@store');

        $router->group( [ 'prefix' => '{other_id}' ], function () use ($router) {
            $router->post('update', 'OtherController@update');
            $router->delete('delete', 'OtherController@delete');
            $router->get('fetch', 'OtherController@fetch');

        });
    });

    $router->group( [ 'prefix' => 'dividers' ], function () use ($router) {
        $router->get('/', 'DividerController@fetchAll');
        $router->post('add', 'DividerController@store');

        $router->group( [ 'prefix' => '{divider_id}' ], function () use ($router) {
            $router->delete('delete', 'DividerController@delete');
            $router->get('fetch', 'DividerController@fetch');
        });
    });

    $router->group( [ 'prefix' => 'headers' ], function () use ($router) {
        $router->get('/', 'HeaderController@fetchAll');
        $router->post('add', 'HeaderController@store');

        $router->group( [ 'prefix' => '{header_id}' ], function () use ($router) {
            $router->post('update', 'HeaderController@update');
            $router->delete('delete', 'HeaderController@delete');
            $router->get('fetch', 'HeaderController@fetch');
        });
    });
});
