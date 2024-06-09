<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Responses\OkResponse;
use App\ModelsExtended\User;
use App\Repositories\Calendars\GoogleCalendars\GoogleCalendarClient;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class GoogleOAuthController extends Controller
{

    /**
     * Begin oAuth
     * Requires authentication
     *
     * @return OkResponse
     * @throws Exception
     */
    public function beginCalendarOAuth(): OkResponse
    {
        $this->validatedRules( [   'redirect_url' => 'required|url' ] );

        $client = new GoogleCalendarClient(auth()->user());
        return new OkResponse( [ "url" => $client->getOAuthenticationURL( request('redirect_url') ) ] );
    }

    /**
     *  Does not require authentication
     *
     * @return RedirectResponse
     */
    public function completeCalendarOAuth(): RedirectResponse
    {
        // Catch Exception here and redirect to APP UI with message
        //
        // On success call artisan to sync all itineraries created for this user
        // with accepted state
        // Then redirect back to APP UI with message

        // you can check success by looking into the token created.

        $state = GoogleCalendarClient::decodeOAuthSate( \request( 'state' ) );

        try {

            $client = new GoogleCalendarClient(User::find( $state['user_id'] ) );
            $client->handleOAuthCallBack();

            // try to update old records
            Artisan::call( 'sync:itinerary-to-google ' . $state['user_id'] );

        }
        catch (\Exception $exception )
        {
            Log::error( "error authenticating user . " , [ $exception->getMessage(), $state , $exception->getTrace() ]  );
        }
        finally
        {
            return redirect()->to( $state['redirect_url'] );
        }
    }

    /**
     *  Requires authentication
     *
     * @return OkResponse
     */
    public function revokeCalendarOAuth(): OkResponse
    {
        try {

            if( auth()->user()->getIsGoogleAuthValidatedAttribute() )
            {
                $client = new GoogleCalendarClient( auth()->user() );
                $client->revokeToken();
            }
        }
        catch (\Exception $exception )
        {
            Log::error( "error revoking user authentication" , $exception->getTrace() );
        }
        finally
        {
            return new OkResponse();
        }
    }

}
