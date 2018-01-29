<?php
$router->get('/login', [
    'as' => 'login',
    'uses' => 'AccountController@login'
]);

$router->get('/wx-login', [
    'as' => 'wx-login',
    'uses' => 'AccountController@wechatLogin'
]);

$router->post('/register', [
    'as' => 'register',
    'uses' => 'AccountController@register'
]);

$router->get('/forgot-passpwrd', [
    'as' => 'register',
    'uses' => 'AccountController@forgotPasspwrd'
]);