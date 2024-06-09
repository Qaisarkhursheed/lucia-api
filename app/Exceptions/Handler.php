<?php

namespace App\Exceptions;

use App\Http\Responses\ExpectionFailedResponse;
use App\Http\Responses\OkResponse;
use App\Http\Responses\PreConditionFailedResponse;
use App\Http\Responses\ServerErrorResponse;
use App\Http\Responses\UrlNotFoundResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param Throwable $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  Request  $request
     * @param Throwable $e
     * @return ExpectionFailedResponse|PreConditionFailedResponse|ServerErrorResponse|UrlNotFoundResponse
     *
     * @throws Throwable
     */
    public function render($request, Throwable $e)
    {
        if ($e instanceof NotFoundHttpException || $e instanceof MethodNotAllowedHttpException  )
            return new UrlNotFoundResponse( $e );

        if ($e instanceof ValidationException)
            return new PreConditionFailedResponse( $e->errors() );

        if ($e instanceof QueryException)
        {
            try {

                DuplicationDBRecordException::detectAndThrow($e);
                DeleteDBRecordException::detectAndThrow($e);

                return new ServerErrorResponse( $e );
            }catch ( \Exception $ex ){
                return new ExpectionFailedResponse( errorKeyMessage( $ex->getMessage()  )  );
            }
        }

        return new ExpectionFailedResponse( errorKeyMessage( $e->getMessage()  )  );
//         return parent::render($request, $e);
    }
}
