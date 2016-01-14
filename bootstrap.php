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

if($url === '/'){
	$page = 'index';
} else {
	$page = strtok($url, '/');
	if(substr($page, -4) === '.php'){
		$page = substr($page, 0, -4);
	}
}

if(file_exists(dirname(__FILE__).'/view/'.ucfirst($page).'.php')){
	$class = '\\view\\'.ucfirst($page);
	$view = new $class();
	$view->view($url);
} else {
	$view = new \view\Index();
	$view->view($url);
}
