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

$router->get('/', [
    'as' => 'home',
    'uses' => 'HomeController@index'
]);

$router->get('/list/{typeId}', [
    'as' => 'list',
    'uses' => 'ListController@all'
]);

$router->get('/item/{id}', [
    'as' => 'item',
    'uses' => 'ItemController@get'
]);