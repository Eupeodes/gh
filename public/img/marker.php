<?php
spl_autoload_register(
	function($className){
		require_once(dirname(__FILE__).'/../../'.str_replace('\\', '/', $className).'.php');
	}
);

\lib\Cache::permanent();
$marker = new \lib\Marker(filter_input_array(INPUT_GET));
$marker->show();