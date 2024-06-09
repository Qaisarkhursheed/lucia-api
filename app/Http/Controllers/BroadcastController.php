<?php

namespace App\Http\Controllers;

use App\Http\Responses\PreConditionFailedResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class BroadcastController extends Controller {

    public function authenticate(Request $request)
    {
        try {
            return Broadcast::auth($request);
        }catch (AccessDeniedHttpException $exception){
            return new PreConditionFailedResponse( message(
                sprintf( "%s does not have access to %s. You can grant access in the channels.php file.",
                auth()->user()->name , $request->input("channel_name") ))
            );
        }
    }
}
