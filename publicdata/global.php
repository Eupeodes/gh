<pre><?php

ini_set('display_errors', 'on');
spl_autoload_register(
	function($className){
		require_once dirname(__FILE__).'/../'.str_replace('\\', '/', $className).'.php';
	}
);
$db = \lib\Db::getInstance();

$req = $db->prepare('SELECT MAX(date) FROM global');
$req->execute();
$d = $req->fetch()['max'];

$req = $db->prepare('SELECT * FROM dow WHERE date>=:date_start LIMIT 50');
$req->bindParam(':date_start', $d, PDO::PARAM_STR);
$req->execute();

$date = new \DateTime($d);
$hash = new \view\Hash();
$i = 0;
while($i < 5000){
	$date->add(new \DateInterval('P1D'));
	$d = $date->format('Y-m-d');
	$global = $hash->getHash($d)['global'];
	if(is_null($global)){
		var_dump($global);
		var_dump($d);
		break;
	}
	\model\GlobalHash::save($d, $global->lat, $global->lng);

	$i++;
}