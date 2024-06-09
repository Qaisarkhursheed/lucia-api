<?php

return [
    'apiKey' => env('ZOOM_API_KEY'),
    'apiSecret' => env('ZOOM_API_SECRET'),
    'baseUrl' => env('ZOOM_API_URL'),
    'token_life' => 60 * 60 * 24 * 7, // In seconds, default 1 week
    'authentication_method' => 'jwt', // Only jwt compatible at present
    'max_api_calls_per_request' => '5' // how many times can we hit the api to return results for an all() request
];
