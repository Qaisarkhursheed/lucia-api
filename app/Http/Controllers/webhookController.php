<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ModelsExtended\User;
use Carbon\Carbon;
use App\ModelsExtended\StripeSubscriptionHistory;

class webhookController extends Controller
{
    public function stripeSubscriptionUpdate(Request $request)
    {
        $customer_email = $request['data']['object']['customer_email'];
        $user = User::where('email', $customer_email)->first();
        if($user)
        {
            StripeSubscriptionHistory::create([
                'user_id' => $user->id,
                'role_id' => 3,
                'stripe_subscription' => json_encode($request['data']['object']),
                'status' => $request['data']['object']['status'] === "paid" ? "active" : "not active",

                'plan_interval' => $request['data']['object']['lines']['data'][0]["plan"]["interval"],

                'amount_decimal' => $request['data']['object']['lines']['data'][0]["plan"]["amount"]/100,
                'current_period_start' => Carbon::createFromTimestamp( $request['data']['object']['period_start'] ),
                'current_period_end' => Carbon::createFromTimestamp( $request['data']['object']['lines']['data'][0]['period']['end'] ),
                'start_date' => Carbon::createFromTimestamp( $request['data']['object']['period_start'] ),
            ]);
        }


    }
}

