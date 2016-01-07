<?php
/*
 * file:		bootstrap.php
 * author:		Marten Tacoma
 * contents:	wrapper for all php files
 */

spl_autoload_register(
	function($className){
		require_once(dirname(__FILE__).'/'.str_replace('\\', '/', $className).'.php');
	}
);

$data = filter_input_array(INPUT_GET);
$url = strtok(filter_input(INPUT_SERVER, 'REQUEST_URI'), '?');

if(is_null($data)){
	define('DEBUG', false);
    $file = 'index.php';
} else {
	define('DEBUG', array_key_exists('debug', $data));
	$file = strtok($url, '/');
}
if(substr($file, -4) === '.php' && strpos($file, '..') === false){
	$page = substr($file, 0, -4);
} else {
	$page = $file;
	$file .= '.php';
}

if(file_exists(dirname(__FILE__).'/view/'.ucfirst($file))){
	$class = '\\view\\'.ucfirst($page);
	$view = new $class($url);
	$view->get();
} else {
	$view = new \view\Index($url);
	$view->get();
}