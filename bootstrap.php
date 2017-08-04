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
	'map'=>['Map', 'Text', 'StaticMap', 'Statbar'],
	'data'=>['DataList', 'Dow', 'Hash', 'MaxDate', 'GeoName']
];
$allowed = $pages[SITE];
$page = strtok($url, '/');
if(strpos($page, '.') !== false){
	$page = strtok($page, '.');
}
if(!in_array(ucfirst($page), $allowed)){
	$page = $allowed[0];
}
ini_set('display_errors', 'on');$class = '\\view\\'.ucfirst($page);
$view = new $class();
$view->view($url);