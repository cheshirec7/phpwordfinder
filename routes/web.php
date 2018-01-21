<?php

//$router->get('/', function () use ($router) {
//    return $router->app->version();
//});


$router->get('/', 'NewController@index');
$router->post('startswith', 'NewController@startswith');
$router->get('loaddb', 'NewController@loaddb');
$router->post('updateword', 'NewController@updateword');

$router->get('find', 'MyController@find');
$router->get('define/{word}', 'MyController@define');
