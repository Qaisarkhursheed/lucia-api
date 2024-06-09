<?php

namespace App\Http\Controllers\Enhancers;

use App\Http\Controllers\Controller;

abstract class CRUDEnabledController extends Controller
{
    use CRUDEnabledTraitController;

    /**
     * Implements CRUDEnabledTraitController
     * @param string $param_name
     * @param string $recordIdentifier
     * @throws \App\Exceptions\RecordNotFoundException
     */
    public function __construct( string $param_name, string $recordIdentifier = "id" )
    {
        $this->invokeLoadRouteModelFunction( $param_name, $recordIdentifier );
    }
}
