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

$router->get('/area/hot-cities', [
    'as' => 'area-hot-cities',
    'uses' => 'AreaController@hotCities'
]);

$router->get('/house/list-by-ids', [
    'as' => 'house-list-by-ids',
    'uses' => 'HouseController@listByIds'
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

$router->get('/house/{id}/like', [
    'as' => 'house-like',
    'uses' => 'HouseController@like'
]);

$router->post('/house/{id}/tour', [
    'as' => 'house-tour',
    'uses' => 'HouseTourController@submit'
]);

$router->get('/house/{id}/source', [
    'as' => 'house-source',
    'uses' => 'HouseController@source'
]);

$router->get('/db-monitor/Us22695Jk', function () {
    $activityRows = \DB::table('pg_stat_activity')->get();
    $houseDataSize = \DB::table('view_house_data_size')->value('size');

    return response()->json([
        'houseData' => [
            'size' => $houseDataSize
        ],
        'activity' => [
            'total' => count($activityRows),
            'items' => $activityRows
        ]
    ]);
});

$router->options('/house/{id}/tour', function () {
    echo 'ok';
});