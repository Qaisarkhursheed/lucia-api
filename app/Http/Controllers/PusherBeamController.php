<?php

namespace App\Http\Controllers;

use App\Repositories\Pusher\PusherBeam;
use Illuminate\Http\Request;

class PusherBeamController extends Controller {

    /**
     * @param Request $request
     * @param PusherBeam $beam
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Exception
     */
    public function authenticate(Request $request, PusherBeam $beam)
    {
        $userID = $request->user()->id; // If you use a different auth system, do your checks here
        $userIDInQueryParam = $request->input('user_id');

        if ($userID != $userIDInQueryParam) {
            return response('Inconsistent request', 401);
        } else {

            $beamsToken = $beam->client()->generateToken(strval($userID));
            return response()->json($beamsToken);
        }
    }
}
