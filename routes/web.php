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

/** POST routes to the private API */
Route::post('api', 'PrivateApiController@index')->name('api');
Route::post('api/{name}', 'PrivateApiController@single')->name('api.single');

/** GET routes to the private API - work in the development environment only */
if(\App::environment('development')) {
    Route::get('api', 'PrivateApiController@index');
    Route::get('api/{name}', 'PrivateApiController@single');
}

// User Activation Route
Route::get('user/activate/{username}/{token}',
    'Auth\ActivateController@activate')->name('user.activate');

// Redirection to frontend - MUST STAY LAST (is fallback)
Route::get('/{route?}', 'AppController@app')
    ->where('route', '.*')
    ->name('app');