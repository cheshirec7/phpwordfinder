<?php

//$router->get('/', function () use ($router) {
//    return $router->app->version();
//});


$router->get('find', 'MyController@find');
$router->get('define/{word}', 'MyController@define');
