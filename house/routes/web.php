<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return 'USLEJU API V2.0';
});

$router->get('/areas/', [
    'as' => 'areas',
    'uses' => 'AreaController@all'
]);

$router->get('/house/search', [
    'as' => 'house-search',
    'uses' => 'HouseController@search'
]);

$router->get('/house/map-search', [
    'as' => 'house-map-search',
    'uses' => 'HouseController@mapSearch'
]);

$router->get('/house/top/items', [
    'as' => 'house-top-items',
    'uses' => 'HouseController@top'
]);

$router->get('/house/{type}/search-options/', [
    'as' => 'house-search-options',
    'uses' => 'HouseController@searchOptions'
]);

$router->get('/house/{id}/', [
    'as' => 'house-get',
    'uses' => 'HouseController@get'
]);

$router->get('/house/{id}/nearbiy', [
    'as' => 'house-nearbiy',
    'uses' => 'HouseController@nearbiy'
]);
