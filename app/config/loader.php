<?php

$loader = new \Phalcon\Loader();

$loader->registerDirs(
	[
		$config->application->controllersDir,
		$config->application->modelsDir
	]
);

$loader->registerNamespaces(
	[
		'App\Forms'  => APP_PATH .'/forms/',
		'App\Library' => APP_PATH .'/library/',
		'App\Elements' => APP_PATH . '/plugins/elements/'
	]
);

/*$loader->registerNamespaces(
	[
		'App\Core' => APP_PATH,
		'App\Core\Controllers' => $config->application->controllersDir,
		'App\Forms'  => APP_PATH .'/forms/',
		'App\Library' => APP_PATH .'/library/',
		'App\Elements' => APP_PATH . '/plugins/elements/',
		'App\Tools\Stats' => APP_PATH . '/plugins/tools/stats/controllers',
	]
);*/

$loader->register();
