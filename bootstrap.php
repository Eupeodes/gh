<?php
/*
 * file:		bootstrap.php
 * author:		Marten Tacoma
 * contents:	wrapper for all php files
 */

spl_autoload_register(
	function($className){
		require_once dirname(__FILE__).'/'.str_replace('\\', '/', $className).'.php';
	}
);

$data = filter_input_array(INPUT_GET);
$url = strtok(filter_input(INPUT_SERVER, 'REQUEST_URI'), '?');

define('DEBUG', is_null($data) ? false : array_key_exists('debug', $data));

$pages = [
	'map'=>['Map', 'text'=>'Text', 'staticmap'=>'StaticMap', 'statbar'=>'Statbar', 'globals'=>'Globalmap', 'mobile'=>'Mobile'],
	'data'=>['DataList', 'dow'=>'Dow', 'hash'=>'Hash', 'maxdate'=>'MaxDate', 'geoname'=>'GeoName']
];
$allowed = $pages[SITE];
$page = strtok($url, '/');
if(strpos($page, '.') !== false){
	$page = strtok($page, '.');
}

$page = strtolower($page);
if(empty($page) || !array_key_exists($page, $allowed)){
	$page = 0;
}
$class = '\\view\\'.$allowed[$page];
$view = new $class();
$view->view($url);