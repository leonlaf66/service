<?php
$router->get('/list/{type}/{id}', [
    'uses' => 'CommentController@all'
]);

$router->post('/submit/{type}/{id}', [
    'uses' => 'CommentController@submit'
]);

$router->options('/submit/{type}/{id}', function () {
    echo 'ok';
});