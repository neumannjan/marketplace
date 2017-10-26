<?php

namespace App\Http\Controllers;

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
