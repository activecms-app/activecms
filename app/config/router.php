<?php

$router = $container->getRouter();

$router->add('/', ['controller' => 'index', 'action' => 'index']);
$router->add('/user/login', ['controller' => 'user', 'action' => 'login']);
$router->add('/user/login/submit', ['controller' => 'user', 'action' => 'loginSubmit']);
$router->add('/user/logout', ['controller' => 'user', 'action' => 'logout']);
$router->add('/object/:action/:params', ['controller' => 'object', 'action' => 1, 'params' => 2]);
$router->add('/media/:action/:params', ['controller' => 'media', 'action' => 1, 'mediafolder' => 2]);
$router->add('/reference/:action', ['controller' => 'reference', 'action' => 1]);


//$router->handle();
