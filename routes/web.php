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

//$router->get('/', function () use ($router) {
//    return $router->app->version();
//});

$router->get('/', 'NewController@index');
$router->post('startswith', 'NewController@startswith');
$router->get('loaddb', 'NewController@loaddb');
$router->post('updateword', 'NewController@updateword');

$router->get('find', 'MyController@find');
$router->get('define/{word}', 'MyController@define');
