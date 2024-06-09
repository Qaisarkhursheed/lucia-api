<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BeamPermissionController extends Controller
{

    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     * @throws \Exception
     */
    public function index(Request $request)
    {
        return view('beam_testing');
    }

    public function prompt(Request $request)
    {
        return view('beam_prompt');
    }
}
