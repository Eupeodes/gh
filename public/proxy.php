<?php

/*
 * file:		proxy.php
 * author:		Marten Tacoma
 * contents:	wrapper for all php files
 */

ini_set('display_errors', 'on');

spl_autoload_register(
	function($className){
		require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT').'/../'.str_replace('\\', '/', $className).'.php');
	}
);

$url = filter_input(INPUT_GET, 'url');
if(substr($url, -4) === '.php'){
	$file = $url;
} else {
	$cutpoint = strpos($url, '/');
	$file = ($cutpoint === false) ? $url : substr($url, 0, strpos($url, '/'));
	$file .= '.php';
}

if(file_exists($file)){
	require($file);
} elseif(preg_match('/('.\lib\RegExp::date().'|'.\lib\RegExp::mapType().'|'.\lib\RegExp::graticule().'|'.\lib\RegExp::hash().'|'. \lib\RegExp::zoom().')/i', filter_input(INPUT_GET, 'url')) || in_array(filter_input(INPUT_GET, 'url'), array('s', 'single'))){
	require('index.php');
} else {
	\lib\Error::send(404, 'The page \''.$url.'\' could not be found');
}
