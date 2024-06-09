<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Routing\Controller as BaseController;
use Nette\Utils\Validators;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\SemaphoreStore;

class Controller extends BaseController
{
    /**
     * Derive auth token if user is logged in
     * @param Request $request
     * @return \Illuminate\Support\Stringable
     */
    protected function getAuthToken( Request $request)
    {
        return Str::of(  $request->header( 'Authorization', $request->input( 'token' ) ) )
            ->ltrim( 'Bearer' )->trim();
    }

//    // ------------------------------------------
//    // MOVED TO Exceptions\Handler
//    // ------------------------------------------
//
//    /**
//     * Helps you run your code in a validated response environment
//     * @param \Closure $function
//     * @return ExpectionFailedResponse|PreConditionFailedResponse|mixed
//     */
//    public function validatedResponse(\Closure $function )
//    {
//        try {
//
//            return $function( \request() );
//
//        } catch (ValidationException $e) {
//            return new PreConditionFailedResponse( $e->errors() );
//        } catch (QueryException  $e) {
//            return new ServerErrorResponse( $e );
//        } catch (\Exception $exception )
//        {
//            return new ExpectionFailedResponse( errorKeyMessage( $exception->getMessage()  )  );
//        }
//    }
//
//    /**
//     * Helps you run your code in a validated response environment
//     * @param \Closure $function
//     * @return ExpectionFailedResponse|PreConditionFailedResponse|mixed
//     */
//    public function validatedResponseWithRules( array $rules, \Closure $function )
//    {
//        return $this->validatedResponse(function () use ( $rules, $function ) {
//            $this->validate( \request(), $rules);
//            return $function( \request() );
//        });
//    }
//    // ------------------------------------------

    /**
     * Gets the action called for this root.
     * Returns path if no action used
     * @return string
     */
    protected function getRequestActionCalled()
    {
       return collect(explode( "@", \request()->route()[1]["uses"] ))->last();
    }

    /**
     * Get route parameter if it exists
     * @param string $param_name
     * @return mixed
     */
    protected function getRouteParam( string $param_name )
    {
        return \request()->route( $param_name );
    }

    /**
     * @param string $param_name
     * @param \Closure $closure
     * @return bool
     */
    protected function canLoadOnResource( string $param_name , \Closure $closure ): bool
    {
        $routeParamValue = $this->getRouteParam( $param_name );

        // This will not run if the param_name is not passed
        // or if it's value is not evaluated as a real value
        if( $routeParamValue &&  $this->shouldLoadResource() )
        {
            $closure (
                $routeParamValue,
                Str::startsWith( $this->getRequestActionCalled() , "fetch" )
            );
            return true;
        }
        return false;
    }

    /**
     * Indicate if resource model should be loaded
     * @return bool
     */
    protected function shouldLoadResource(): bool
    {
        return ! in_array( $this->getRequestActionCalled(), [ 'fetchAll', 'add' ] );
    }

    /**
     * Validates your rules
     * It returns validated array
     *
     * @throws ValidationException
     */
    public function validatedRules( array $rules ): array
    {
         return $this->validate( \request(), $rules);
    }

    /**
     * Run function in a synchronous manner
     * @param string $lock_identifier   Unique string to identify the function
     * @param \Closure $closure
     * @param int $ttl                  Maximum expected lock duration in seconds
     * @return mixed
     * @throws \Exception
     */
    public function runInALock( string $lock_identifier, \Closure $closure, int $ttl = 30 )
    {
        // This will be locked down to ensure consistency
        $factory = new LockFactory( new SemaphoreStore() );

        // create an expiring lock that lasts 30 seconds
        $lock = $factory->createLock($lock_identifier, $ttl );

        if (!$lock->acquire()) {
            throw new \Exception( "Sorry, there are too many request and the server is busy!");
        }
        try {

            return $closure();

        } finally {
            $lock->release();
        }
    }

    /**
     * Checks if all emails in array are valid emails
     * @param array $emails
     * @return bool
     */
    public function isValidEmailArray( array $emails ): bool
    {
        return count( $emails ) &&
            ! collect( $emails )->filter( function ( string $email ){
                return ! Validators::isEmail( $email );
            })->count();
    }
}
