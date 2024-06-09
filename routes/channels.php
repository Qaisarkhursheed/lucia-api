<?php

use App\ModelsExtended\AdvisorRequest;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

//Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//    return (int) $user->id === (int) $id;
//});

// you must not use the private- here. Just use the channel name.
// even thou it is used in parameter
//Broadcast::channel('unamed', function ($user) {
//    return true;
//});

// you must not use the private- here. Just use the channel name.
// even thou it is used in parameter
Broadcast::channel('concierge-live-chat.{user_id}-{advisor_id}', function ($user,
                                                                           int $user_id, int $advisor_id) {
    // You are either owner or copilot
    $advisor = AdvisorRequest::getById($advisor_id);
    if( $advisor && (
            $advisor->created_by_id === $user_id
            || optional($advisor->advisor_assigned_copilot)->copilot_id === $user_id
        )) return true;
    return false;
});

Broadcast::channel('lucia.12', function () {
    return true;
});
