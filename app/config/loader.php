<?php

$loader = new \Phalcon\Loader();

/**
 * We're a registering a set of directories taken from the configuration file
 */
$loader->registerDirs(
    [
        $config->application->controllersDir,
        $config->application->modelsDir
    ]
);

// Register some namespaces
$loader->registerNamespaces(
    [
       'App\Forms'  => APP_PATH .'/forms/',
	   'App\Library' => APP_PATH .'/library/',
	   'App\Elements' => APP_PATH . '/plugins/elements/'
    ]
);

$loader->register();
