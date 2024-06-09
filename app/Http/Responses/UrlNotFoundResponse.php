<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class UrlNotFoundResponse extends JsonResponse
{
    /**
     * @param NotFoundHttpException | MethodNotAllowedHttpException $e
     * @param array $headers
     * @param int $options
     */
    public function __construct(  $e, $headers = [], $options = 0)
    {
        parent::__construct(  array_merge(
            message( "Url or Method or Verb not found." ),
            [ "url" => request()->fullUrl() ],
            isAppInDebugMode() ? $e->getTrace() : [ "Trace is only available in Debug Mode" ],
        ), Response::HTTP_NOT_FOUND, $headers, $options);
    }
}
