<?php

namespace App\Exceptions;

use App\Repositories\ApiInvoker;
use Illuminate\Http\Response;

class APIInvocationException extends \Exception
{
    public ApiInvoker $invoker;
    public function __construct( ApiInvoker $invoker )
    {
        $this->invoker = $invoker;
        parent::__construct( 'Error occurred. ' . optional( $invoker->getData() )->message );
    }


    /**
     * Report the exception.
     *
     * @return bool|null
     */
    public function report()
    {
        // return null or true to indicate you have handled it
        // return false to let laravel handle using default logging
//        return true;

        if( isAppInDebugMode() )
        {
            info( $this->invoker->getResponseStatus(), (array) $this->invoker->getData()  );
        }

        return true;
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function render(  $request )
    {
        return response()->json( $this->invoker->getData(), $this->invoker->getResponseCode() );
    }

}
