<?php

namespace App\Http\Controllers;

use App\Mail\Agent\AdvisorRequestCompletedMail;
use App\Mail\Copilot\NewRequestReceivedMail;
use App\ModelsExtended\AdvisorRequest;
use App\ModelsExtended\User;
use App\Repositories\Stripe\StripeConnectSDK;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TestController extends Controller
{

    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     * @throws \Exception
     */
    public function index(Request $request, StripeConnectSDK $SDK)
    {
        // send notification of payment
//        $SDK->confirmPaymentIntentWithCard("pi_3LTQLxCL9jn4IH9s1oVCViId", 'tok_1LTQMGCL9jn4IH9stcAAHeep' );


//        return redirect('https://svr2.scadware.com:14443/beam?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vdWJ1bnR1LWRlbGw6MTQwODAvYXV0aC9sb2dpbiIsImlhdCI6MTY1OTQ0MzAyMywiZXhwIjoxNjU5NDc5MDIzLCJuYmYiOjE2NTk0NDMwMjMsImp0aSI6Im5XQzBQZ0U4TFJtdmlDTlYiLCJzdWIiOiIzIiwicHJ2IjoiMjg3MTMyYTkzZDNlYjE2MWRkNGY1NzNhOGUxYzliMWY3NTM1ZDViOCJ9.Su6Ohh8xUqqlQqHr22OdJVtI9mWU2mELO_RAIZZAboU&userId=3&redirect_url=https://www.google.com');

        return response()->json(message("Getting Started"));
    }
    
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     * @throws \Exception
     */
    public function index2(Request $request, StripeConnectSDK $SDK)
    {
        
        return response()->json(message("Getting Started 2"));
    }
}
