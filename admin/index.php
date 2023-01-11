<?php
use Phalcon\Mvc\Application;

//TimeZone
date_default_timezone_set("Chile/Continental");
//Cambia nombre de sesion (ocultar PHP)
session_name('WASESSID');
session_start();

use Phalcon\Di\FactoryDefault;
use Phalcon\Security\Random;

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

try {

	$container = new FactoryDefault();
	include APP_PATH . '/config/router.php';
	include APP_PATH . '/config/services.php';
	$config = $container->getConfig();
	$random = new Random();
	//Establece llave de sesion x 1 mes
	if( !isset($_COOKIE['WASESSION']) ) {
		setcookie('WASESSION', sha1(uniqid(rand(), true)), time() + 60 * 60 * 24 * 30, '/', 'netwalker.cl');
	}
	include APP_PATH . '/config/loader.php';
	$application = new Application($container);
	$response = $application->handle(str_replace('/active', '', $_SERVER['REQUEST_URI']));
	$response->send();
} catch (\Exception $e) {
	echo $e->getMessage() . '<br>';
	echo '<pre>' . $e->getTraceAsString() . '</pre>';
}
