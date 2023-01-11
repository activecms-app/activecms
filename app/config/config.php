<?php
use Phalcon\Config;
use Phalcon\Config\ConfigFactory;

defined('BASE_PATH') || define('BASE_PATH', getenv('BASE_PATH') ?: realpath(dirname(__FILE__) . '/../..'));
defined('APP_PATH') || define('APP_PATH', BASE_PATH . '/app');

if( isset($_SERVER['SERVER_NAME']) ) {
	$configName = $_SERVER['SERVER_NAME'];
} else {
	$configName = 'default';
}

$baseConfig = new Config(
	[
		'application' => [
			'appDir'         => APP_PATH . '/',
			'controllersDir' => APP_PATH . '/controllers/',
			'modelsDir'      => APP_PATH . '/models/',
			'migrationsDir'  => APP_PATH . '/migrations/',
			'viewsDir'       => APP_PATH . '/views/',
			'pluginsDir'     => APP_PATH . '/plugins/',
			'libraryDir'     => APP_PATH . '/library/',
			'cacheDir'       => BASE_PATH . '/cache/'
		]
	]
);

if( isset($_SERVER['SERVER_NAME']) ) {
	$configFile = BASE_PATH . '/config/' . $_SERVER['SERVER_NAME'] . '.php';
	$factory  = new ConfigFactory();
	$configWeb = $factory->newInstance('php', $configFile);
	if( $configWeb ) {
		$baseConfig->merge($configWeb);
	}
}

return $baseConfig;
