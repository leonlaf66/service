<?php

$router->get('/profile', [
    'as' => 'profile',
    'uses' => 'ProfileController@get'
]);

$router->get('/favorite/list', [
    'as' => 'favorite-list',
    'uses' => 'FavoriteController@list'
]);

$router->get('/favorite/remove', [
    'as' => 'favorite-remove',
    'uses' => 'FavoriteController@remove'
]);

$router->get('schedule/list', [
    'as' => 'schedule-list',
    'uses' => 'ScheduleController@list'
]);

$router->get('schedule/remove', [
    'as' => 'schedule-remove',
    'uses' => 'ScheduleController@remove'
]);
