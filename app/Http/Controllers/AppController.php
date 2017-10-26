<?php

namespace App\Http\Controllers;

/**
 * Controller for the application frontend
 */
class AppController extends Controller
{
    /**
     * Show the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function app()
    {
        return view('app');
    }
}
