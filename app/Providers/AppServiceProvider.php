<?php

namespace App\Providers;

use App\ModelsExtended\User;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // if this is not created once
        // queue isn't working
        app('queue');

        Carbon::macro(
            'fromPreferredTimezoneToAppTimezone',
            /**
             * @returns Carbon
             */
            function ( ) {
                return  $this->clone()
                    // Value is already in User Timezone
                    ->shiftTimezone(auth()->user()->getTimezone())
                    // RESET to app timezone UTC changing the value as well
                    ->setTimezone( env( 'APP_TIMEZONE' )  );
            }
        );

        Carbon::macro(
            'fromUserPreferredTimezoneToAppTimezone',
            /**
             * @returns Carbon
             */
            function ( User $user  ) {
                return  $this->clone()
                    // Value is already in User Timezone
                    ->shiftTimezone($user->getTimezone())
                    // RESET to app timezone UTC changing the value as well
                    ->setTimezone( env( 'APP_TIMEZONE' )  );
            }
        );

        Carbon::macro(
            'fromAppTimezoneToPreferredTimezone',
            /**
             * @returns Carbon
             */
            function (  ) {
               return $this->fromAppTimezoneToUserPreferredTimezone( auth()->user() );
            }
        );

        Carbon::macro(
            'fromAppTimezoneToUserPreferredTimezone',
            /**
             * @returns Carbon
             */
            function ( User $user ) {

                return $this->clone()
                    // Value is already in UTC
                    // change with value to user real timezone
                    ->setTimezone( $user->getTimezone())
                    // shift it because app will convert back to UTC when outputting
                    // To prevent app reversing it again
                    ->shiftTimezone(  env( 'APP_TIMEZONE' ) );
            }
        );

    }
}
