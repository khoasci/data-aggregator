<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::any('/', function () {
    return redirect('/api/v1');
});


Route::group(['prefix' => 'v1'], function() {

    Route::any('/', function () {
        return redirect('/api/v1/swagger.json');
    });

    Route::any('swagger.json', function() {
        return response(view('swagger'), 200, ['Content-Type' => 'application/json']);
    });

    // Elasticsearch
    Route::match( array('GET', 'POST'), 'search', 'SearchController@search');
    Route::match( array('GET', 'POST'), '{resource}/search', 'SearchController@search');
    // We can do ->where('resource', '(foo|bar)') to limit {resource}, but it's not necessary...

    Route::match( array('GET', 'POST'), 'msearch', 'SearchController@msearch');

    Route::match( array('GET', 'POST'), 'autocomplete', 'SearchController@autocompleteWithTitle');
    Route::match( array('GET', 'POST'), 'autosuggest', 'SearchController@autocompleteWithSource');

    // For debugging search, show generated request
    if( env('APP_ENV') === 'local' ) {
        Route::match( array('GET', 'POST'), 'echo', 'SearchController@echo');
        Route::match( array('GET', 'POST'), '{resource}/echo', 'SearchController@echo');
        Route::match( array('GET', 'POST'), '{resource}/{id}/explain', 'SearchController@explain');
    }

    // Define all of our resource routes by looping through config
    foreach(config('resources.outbound.base') as $resource)
    {
        $isScoped = $resource['scope_of'] ?? false;

        Route::any($resource['endpoint'], 'ResourceController@' . ($isScoped ? 'indexScope' : 'index'));
        Route::any($resource['endpoint'] . '/{id}', 'ResourceController@' . ($isScoped ? 'showScope' : 'show'));
    }

});
