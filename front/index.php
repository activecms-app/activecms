<?php
use Phalcon\DI;
use Phalcon\DI\FactoryDefault;
use Phalcon\Mvc\View\Simple as View;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Http\Response\Cookies;
use Phalcon\Http\Request;

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

$container = new FactoryDefault();

$container->setShared('config', function () {
	return include APP_PATH . "/config/config.php";
});

$loader = new \Phalcon\Loader();
$loader->registerDirs([
	$container->getConfig()->application->modelsDir
]);
$loader->registerNamespaces(	[
	'App\Elements' => APP_PATH . '/plugins/elements/'
]);
$loader->register();

$container->setShared('db', function () {
    $config = $this->getConfig();

    $class = 'Phalcon\Db\Adapter\Pdo\\' . $config->database->adapter;
    $params = [
        'host'     => $config->database->host,
        'username' => $config->database->username,
        'password' => $config->database->password,
        'dbname'   => $config->database->dbname,
        'charset'  => $config->database->charset
    ];

    $connection = new $class($params);

    return $connection;
});

//Web
$web = Webs::getPublished($_SERVER['SERVER_NAME']);
if( !$web )
{
	die("Sitio web no configurado");
}

$container->setShared("view", function () {
	global $web;
	$config = $this->getConfig();
	$view = new View();

	$view->setDI($this);
	if( $this->getRequest()->has('_view') ) {
		$_view = $this->getRequest()->get('_view');
		if( empty($_view) ) {
			if( isset($_COOKIE['_view']) ) {
				unset($_COOKIE['_view']);
				setcookie('_view', null, -1, '/');
			}
			//$this->getCookies()->delete('_view');
		}
		else {
			setcookie('_view', $_view, time() + 86400 * 365, '/');
			//$this->getCookies()->set('_view', $_view, $expire);
		}
	} elseif( isset($_COOKIE['_view']) ) { //elseif( $this->getCookies()->has('_view') ) {
		$_view = $_COOKIE['_view'];
	} else {
		$_view = $web->theme->Name;
	}
	//TODO: check if the view dir exists
	if( empty($_view) ) {
		$_view = 'en'; //TODO: take from de web theme
		if( substr($_REQUEST['_url'], 0, 4) == '/es/' ) {
			$_view = 'es';
		}
	}
	$view->setViewsDir($config->front->templatesDir . $_view);

	$view->registerEngines([
		".volt" => function ($view) {
			$config = $this->getConfig();
			$volt = new VoltEngine($view, $this);
			$volt->setOptions([
				"path"      => $config->front->cacheDir,
				"separator" => "_",
			]);
			$compiler = $volt->getCompiler();
			$compiler->addFunction('setlocaltime', 'wa_setlocaltime');
			$compiler->addFunction('dateformat', 'strftime');
			$compiler->addFunction('array_push', 'array_push');
			$compiler->addFunction('array_merge', 'array_merge');
			$compiler->addFunction('http_build_query', 'http_build_query');
			$compiler->addFunction('number_format', 'number_format');
			$compiler->addFunction('wa_menu', 'wa_menu');
			$compiler->addFunction('replace','str_replace');
			$compiler->addFunction('substr','mb_substr');
			$compiler->addFunction('strpos', 'mb_strpos');
			$compiler->addFunction('files', 'volt_function_files');
			$compiler->addFunction('filesX', 'volt_function_filesX');
			$compiler->addFunction('folders', 'volt_function_folders');
			$compiler->addFunction('foldersX', 'volt_function_foldersX');
			$compiler->addFunction('getobject', 'volt_function_getobject');
			$compiler->addFunction('getobjectX', 'volt_function_getobjectX');
			$compiler->addFunction('getfileX', 'volt_function_getfileX');
			$compiler->addFunction('paginate', 'volt_paginate');
			$compiler->addFunction('countFiles', 'volt_countfiles');
			$compiler->addFunction('countFolders', 'volt_countfolders');
			$compiler->addFunction('filter', 'volt_function_filter');
			$compiler->addFunction('search', 'volt_function_search');
			$compiler->addFunction('filterObjects', 'volt_function_filterobjects');
			$compiler->addFunction('filterObjectsX', 'volt_function_filterobjectsX');
			$compiler->addFunction('getElementByName', 'volt_getElementByName');
			$compiler->addFunction('sql_getresults', 'volt_sql_getresults');
			$compiler->addFunction('logs_visits', 'volt_logs_visits');
			$compiler->addFunction('medwave_usuario', 'medwave_usuario');
			$compiler->addFilter('split', 'volt_filter_split');
			$compiler->addFilter('tagsname', 'volt_filter_tagsname');
			$compiler->addFilter('medwave_reference', 'medwave_filter_reference');
			$compiler->addFilter('addlink', 'volt_addlink');
			$compiler->addFilter('format', 'volt_format');
			return $volt;
		}

	]);

		return $view;
	}
);

try
{
	$objectspaths = $web->getObjectsPath($_REQUEST['_url']);
}
catch (Exception $e)
{
	die($e->getMessage());
}

$container->getView()->setVar('web', $web->toFront());
if( substr($_REQUEST['_url'], -5) == '.html' )
{
	$pathDir = '/';
	if( count($objectspaths) > 1)
	{
		$path = [];
		for($i = 0; $i < count($objectspaths) - 1; $i++ )
		{
			$path[$i] = $objectspaths[$i]->toFront($web, false, $pathDir);
			if( $i == count($objectspaths) - 1 )
			{
				$container->getView()->setVar('folder', $objectspaths[$i]->toFront($web, true, $pathDir));
			}
			$pathDir .= $objectspaths[$i]->Name . '/';
		}
		$container->getView()->setVar('path', $path);
	}
	$container->getView()->setVar('file', end($objectspaths)->toFront($web, true, $pathDir));
	end($objectspaths)->log();
	$template = end($objectspaths)->type->Template;
}
elseif( count($objectspaths) > 0)
{
	$path = [];
	$pathDir = '/';
	for($i = 0; $i < count($objectspaths); $i++ ) {
		$path[$i] = $objectspaths[$i]->toFront($web, false, $pathDir);
		if( $i == count($objectspaths) - 1 ) {
			$container->getView()->setVar('folder', end($objectspaths)->toFront($web, true, $pathDir));
		}
		$pathDir .= $objectspaths[$i]->Name . '/';
	}
	$container->getView()->setVar('path', $path);
	$template = end($objectspaths)->type->Template;
	end($objectspaths)->log();
}
else
{
	$container->getView()->setVar('folder', $web->object->toFront($web, true, $pathDir));
	$template = $web->object->type->Template;
	$web->object->log();
}

if( isset($_REQUEST['tpl']) )
{
	$template = $_REQUEST['tpl'];
}

// Render a view
echo $container->getView()->render($template);

function wa_menu($code, $num = null)
{
	global $web;

	$menu = Menus::findFirst([
		'Code = :code:',
		'bind' => [ 'code' => $code]
	]);
	if( !isset($menu->Id) )
		return null;
	
	$items = $menu->getItemsParent($num, false);
	
	if( count($items) == 0 )
		return null;
	
	$objectlist = [];
	foreach($items as $item)
	{
		array_push($objectlist, $item->toFront($web));
	}
	return $objectlist;
}

function wa_setlocaltime($locale = "es_ES") {
	setlocale(LC_TIME, $locale);
}

function volt_filter_split($value, $separator = '', $length = 0)
{
	if (empty($separator)) {
		return str_split($value, $length);
	}

	if( $length > 0) {
		return explode($separator, $value, $length);
	}

	return explode($separator, $value);
}

function volt_filter_tagsname($value, $tag)
{
	$tagslist = [];
	$doc = new DOMDocument('1.0', 'UTF-8');
	if( $doc->loadHTML('<?xml encoding="utf-8" ?>' . $value) )
	{
		$list = $doc->getElementsByTagName($tag);
		for ($i = 0; $i < $list->length; $i++) {
			$tagslist[] = $list->item($i)->nodeValue;
		}
	}
	else
	{
		$tagslist[] = 'Error procesando contenido';
	}
	return $tagslist;

}

function medwave_usuario()
{
	if( isset($_COOKIE['WAKey']) || isset($_COOKIE['WASession']) ) {
		$sql = "select S.Id Sesion, U.Id, U.Nombre, U.APaterno, U. Profesion ";
		$sql .= "from Medwave.Usuarios U ";
		$sql .= "inner join Medwave.Sesiones S on S.Usuarios_Id = U.Id ";
		$sql .= "where S.SessionKey = :key and now() <= FechaDuracion ";
		$sql .= "order by S.FechaCreacion desc limit 1";
		$result =  DI::getDefault()->getDb()->fetchOne(
			$sql,
			\Phalcon\Db\Enum::FETCH_ASSOC,
			[
				'key' => $_COOKIE['WASession']
			]
		);
		if( $result ) {
			DI::getDefault()->getDb()->query("update Medwave.Sesiones set FechaDuracion = DATE_ADD(FechaDuracion, INTERVAL 60 MINUTE) where Id = " . $result['Sesion']);
			return (object)$result;
		}
	}
	return null;
}

function medwave_filter_reference($value)
{
	$rangeseparators = ['&ndash;', '&mdash;', '-', '–'];

	if( preg_match_all('/\[(\d+|\d+(\,\d+|&ndash;\d+|&mdash;\d+)+)\]/', $value, $matches) ) {
		for($refs = 0; $refs < count($matches[1]); $refs++) {
			$ref = explode(',', $matches[1][$refs]);
			for($refpart = 0; $refpart < count($ref); $refpart++) {
				$refrange = false;
				foreach($rangeseparators as $separator) {
					if( mb_strstr($ref[$refpart], $separator) ) {
						$refrange = true;
						break;
					}
				}
				if( $refrange ) {
					list($minrange, $maxrange) = mb_split($separator, $ref[$refpart]);
					$ref[$refpart] = '';
					for($refnum = $minrange; $refnum <= $maxrange; $refnum++) {
						if( !empty($ref[$refpart]) ) $ref[$refpart] .= ',';
						$ref[$refpart] .= '<a href="#reference_' . $refnum . '" class="reference">' . $refnum . '</a>';
					}
				} else {
					$ref[$refpart] = '<a href="#reference_' . $ref[$refpart] . '" class="reference">' . $ref[$refpart] . '</a>';
				}
			}
			$value = str_replace($matches[0][$refs], '[' . implode(',', $ref) . ']', $value);
		}
	}
	return $value;
}

function wa_getobject($path, $options)
{

}

function volt_function_foldersX($reference, $options = null)
{
	global $web;
	$path = '';

	if( is_numeric($reference) )
	{
		$object = Objects::findFirst($reference);
	}
	else
	{
		try {
			$objectspaths = $web->getObjectsPath($reference);
			$path = $reference;
			$object = end($objectspaths);
		}
		catch(Exception $e)
		{
			return [];
		}
	}

	if( !isset($object->Id) )
	{
		return [];
	}
	if( !$object->isFolder() )
	{
		return [];
	}
	
	$offset = 0;
	$limit = 0;
	$sort = '';
	$sortAsc = false;
	if( !is_null($options) )
	{
		$offset = isset($options['offset']) ? intval($options['offset']) : $offset;
		$limit = isset($options['limit']) ? intval($options['limit']) : $limit;
		$sortAsc = isset($options['asc']) && $options['asc'] ? $sortDir = true : false;
	}

	$folders = $object->getFolders(true, $offset, $limit, $sort, $sortAsc);
	if( count($folders) == 0 )
	{
		return [];
	}

	if( empty($path) )
	{
		$path = $web->getUrlByReference($object->Id);
	}

	$objectlist = [];
	foreach($folders as $folder)
	{
		array_push($objectlist, $folder->toFront($web, true, $path));
	}
	return $objectlist;
}

function volt_function_filesX($reference, $options = null)
{
	global $web;
	$path = '';

	if( is_numeric($reference) )
	{
		$object = Objects::findFirst($reference);
	}
	else
	{
		try {
			$objectspaths = $web->getObjectsPath($reference);
			$path = $reference;
			$object = end($objectspaths);
		}
		catch(Exception $e)
		{
			return [];
		}
	}

	if( !isset($object->Id) )
	{
		return [];
	}
	if( !$object->isFolder() )
	{
		return [];
	}

	$offset = 0;
	$limit = 0;
	$sort = '';
	$sortAsc = false;
	if( !is_null($options) ) {
		$offset = isset($options['offset']) ? intval($options['offset']) : $offset;
		$limit = isset($options['limit']) ? intval($options['limit']) : $limit;
		$sort = isset($options['sort']) ? $options['sort'] : $sort;
		$sortAsc = isset($options['asc']) && $options['asc'] ? true : false;
	}

	$files = $object->getFiles(true, $offset, $limit, $sort, $sortAsc);
	if( count($files) == 0 )
	{
		return [];
	}

	if( empty($path) )
	{
		$path = $web->getUrlByReference($object->Id);
	}

	$objectlist = [];
	foreach($files as $file)
	{
		array_push($objectlist, $file->toFront($web, true, $path));
	}
	return $objectlist;
}

function volt_countfolders($reference, $options = null)
{
}


function volt_countfiles($reference, $options = null) {
	global $web;

	if( is_numeric($reference) ) {
		$object = Objects::findFirst($reference);
	} else {
		try {
			$objectspaths = $web->getObjectsPath($reference);
			$object = end($objectspaths);
		}
		catch(Exception $e) {
			return [];
		}
	}

	if( !isset($object->Id) ) {
		return [];
	}
	if( !$object->isFolder() ) {
		return [];
	}

	$files = $object->getFiles(true);
	return count($files);
}

function volt_function_getobject($reference, $options = null)
{
	global $web;

	if( is_numeric($reference) )
	{
		$object = Objects::findFirst($reference);
		$path = null;
	}
	else
	{
		try {
			$objectspaths = $web->getObjectsPath($reference);
			$object = end($objectspaths);
			$path = $reference;
		}
		catch(Exception $e)
		{
			return [];
		}
	}

	if( !isset($object->Id) )
	{
		return [];
	}

	return $object->toFront($web, false, $path);
}

/*

*/
function volt_function_getobjectX($reference, $options = null)
{
	global $web;

	if( is_numeric($reference) )
	{
		$object = Objects::findFirst($reference);
		$path = null;
	}
	else
	{
		try {
			$objectspaths = $web->getObjectsPath($reference);
			$object = end($objectspaths);
			$path = $reference;
		}
		catch(Exception $e)
		{
			return [];
		}
	}

	if( !isset($object->Id) )
	{
		return [];
	}

	return $object->toFront($web, true, $path);
}

function volt_function_getfilex($reference, $options)
{
	global $web;

	if( is_numeric($reference) )
	{
		$parent = Objects::findFirst($reference);
		$path = null;
	}
	else
	{
		try {
			$objectspaths = $web->getObjectsPath($reference);
			$parent = end($objectspaths);
			$path = $reference;
		}
		catch(Exception $e)
		{
			return [];
		}
	}

	$sort = '';
	$sortAsc = false;
	if( !is_null($options) )
	{
		$sortAsc = isset($options['asc']) && $options['asc'] ? $sortDir = true : false;
	}

	$childs = $parent->getChilds('file', true, 0, 1, '', $sortAsc);

	if( count($childs) == 0 )
	{
		return '';
	}

	return $childs[0]->toFront($web, true, $path);

}

function volt_paginate($total_items, $items_per_page, $current_page)
{
	$pagination = [
		'pages' => 1,
		'current' => 1,
		'offset' => 0,
		'limit' => $items_per_page,
		'first' => false,
		'last' => false,
		'begin' => 0,
		'end' => 0,
		'prev' => 0,
		'next' => 0
	];

	//Total pages
	if( $items_per_page < 1 || $total_items < 1 ) {
		return (object)$pagination;
	}
	if( $total_items % $items_per_page == 0 ) {
		$pagination['pages'] = (integer) ($total_items / $items_per_page);
	}
	else {
		$pagination['pages'] = (integer) ($total_items / $items_per_page) + 1;
	}
	$pagination['current'] = $current_page >= $pagination['pages'] ? $pagination['pages'] : ($current_page > 0 ? $current_page : 1);
	$pagination['offset'] = ($pagination['current'] - 1) * $items_per_page;
	$pagination['first'] = $pagination['current'] == 1;
	$pagination['prev'] = $pagination['current'] > 1 ? $pagination['current'] - 1 : 1;
	$pagination['last'] = $pagination['current'] == $pagination['pages'];
	$pagination['next'] = $pagination['current'] < $pagination['pages'] ? $pagination['current'] + 1 : $pagination['pages'];
	$pagination['begin'] = $pagination['offset'] + 1;
	$pagination['end'] = $pagination['current'] < $pagination['pages'] ? $pagination['offset'] + $items_per_page : $total_items;
	return (object)$pagination;
}

function volt_function_filter($parent, $field, $comparation, $value) {

	//TODO: cath
	$filter = Filters::getFilter($parent, $field, $comparation, $value);
	if( is_null($filter) )
	{
		return '';
	}

	return (object)[
		'id' => $filter->Id,
		'key' => $filter->FilterKey,
		'total' => $filter->Total,
		'date' => $filter->FilterDate
	];
}

function volt_function_search($parent, $value) {

	//TODO: cath
	$filter = Filters::getSearch($parent, $value);
	if( is_null($filter) )
	{
		return '';
	}

	return (object)[
		'id' => $filter->Id,
		'key' => $filter->FilterKey,
		'total' => $filter->Total,
		'date' => $filter->FilterDate
	];
}

function volt_function_filterobjects($filter, $options = null)
{
	global $web;

	$filter = Filters::findFirst($filter);
	if( is_null($filter) )
	{
		return null;
	}

	$offset = 0;
	$limit = 0;
	$sort = '';
	$sortAsc = false;
	if( !is_null($options) )
	{
		$offset = isset($options['offset']) ? intval($options['offset']) : $offset;
		$limit = isset($options['limit']) ? intval($options['limit']) : $limit;
		$sortAsc = isset($options['asc']) && $options['asc'] ? $sortDir = true : false;
	}

	$objects = $filter->getObjects(false, $offset, $limit, $sort, $sortAsc);
	$objectlist = [];
	foreach($objects as $object)
	{
		array_push($objectlist, $object->toFront($web, false));
	}
	return $objectlist;
}

function volt_function_filterobjectsX($filter, $options = null)
{
	global $web;

	$filter = Filters::findFirst($filter);
	if( is_null($filter) )
	{
		return null;
	}

	$offset = 0;
	$limit = 0;
	$sort = '';
	$sortAsc = false;
	if( !is_null($options) )
	{
		$offset = isset($options['offset']) ? intval($options['offset']) : $offset;
		$limit = isset($options['limit']) ? intval($options['limit']) : $limit;
		$sortAsc = isset($options['asc']) && $options['asc'] ? $sortDir = true : false;
		$sort = isset($options['sort']) && $options['sort'] ? $options['sort'] : '';
	}

	$objects = $filter->getObjects($offset, $limit, $sort, $sortAsc);
	$objectlist = [];
	foreach($objects as $object) {
		array_push($objectlist, $object->toFront($web, true));
	}
	return $objectlist;
}

function volt_addlink($value, $text = '', $target = '_blank') {
	if( empty($text) ) {
		return preg_replace('"\b(https?://\S+)"', '<a target="' . $target . '" href="$1">$1</a>', $value);
	}
	return preg_replace('"\b(https?://\S+)"', '<a target="' . $target . '" href="$1">' . $text . '</a>', $value);
}

function volt_format($value, $decimals = 0) {
	return number_format($value, $decimals, ',', '.'); //TODO: dependiente del lenguaje de la pagina
}

function volt_getElementByName($value, $tag) {
	$result = [];
	$doc = new DOMDocument();
	libxml_use_internal_errors(true);
	$doc->loadHTML($value);
	$elements = $doc->getElementsByTagName($tag);
	if( $elements ) {
		foreach($elements as $element) {
			//print_r($element);
			$result[] = $element->ownerDocument->saveHTML($element);
		}
	}
	return $result;
}

function volt_logs_visits($object_id) {
	return ObjectsLogs::count([
		'Objects_Id = :id:',
		'bind' => [
			'id' => $object_id
		]
	]);
}

function volt_sql_getresults($query, $params = null) {
	$result =  DI::getDefault()->getDb()->fetchAll(
		$query,
		\Phalcon\Db\Enum::FETCH_ASSOC,
		$params
	);
	return $result;
}
