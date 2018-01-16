<?php
$router->get('/banners', [
    'as' => 'banners',
    'uses' => 'SupportController@banners'
]);

$router->get('/types', [
    'as' => 'types',
    'uses' => 'SupportController@types'
]);

$router->get('/list/', [
    'as' => 'list',
    'uses' => 'ListController@all'
]);

$router->get('/item/{id}', [
    'as' => 'get',
    'uses' => 'ItemController@get'
]);