<?php

spl_autoload_register(
	function($className){
		require_once dirname(__FILE__).'/'.str_replace('\\', '/', $className).'.php';
	}
);
define('BASE_DIR', dirname(__FILE__));

$twitter = new \cron\Twitter();
$ircBot = new \cron\IrcBot();

$ircBot->cron();

//do all the daily stuff if new dow is available
$dowDate = \model\Date::max();
if(\model\Dow::getNew()){
	$twitter->dailyCoords($dowDate);
	$ircBot->dailyCoords($dowDate);
}

$wiki = new \cron\Wiki();
$wiki->cron();

$twitter->sendQueue();
