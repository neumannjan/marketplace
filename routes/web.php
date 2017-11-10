<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/** POST route to the internal API */
Route::post('api', 'InternApiController@index');
/** GET route to the internal API - works in the development environment only */
Route::get('api', 'InternApiController@index')->middleware('dev');

// User Activation Route
Route::get('user/activate/{username}/{token}', 'Auth\ActivateController@activate')->name('user.activate');

// MUST STAY LAST
Route::get('/{route?}', 'AppController@app')
    ->where('route', '.*')
    ->name('app');