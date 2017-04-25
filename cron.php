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

//option to trigger dailyCoords from the db
$db = \lib\Db::getInstance();
$q = $db->prepare('SELECT val FROM conf WHERE field=\'trigger_post_date\'');
$q->execute();
$trigger_post_date = $q->fetch(PDO::FETCH_OBJ)->val;
if(!is_null($val)){
	$twitter->dailyCoords($val);
	$ircBot->dailyCoords($val);
	$db->prepare('UPDATE conf SET val=null WHERE field=\'trigger_post_date\'')->execute();
}

//do all the daily stuff if new dow is available
$dowDate = \model\Date::max();
if(\model\Dow::getNew()){
	$twitter->dailyCoords($dowDate);
	$ircBot->dailyCoords($dowDate);
}

$wiki = new \cron\Wiki();
$wiki->cron();

$twitter->sendQueue();
