<?php

namespace cron;

use \PDO;

class IrcBot {
	private $db;
	function __construct() {
		$this->db = \lib\Db::getInstance();
	}
	
	public function cron(){
		$this->globalToday();
	}

	public function send($msg){
		file_put_contents('/var/www/gh/v1/hashie/db/fileReaderOutput.db', $msg."\r\n", FILE_APPEND);
	}
	
	public function dailyCoords($date){
		$this->hash = new \view\Hash();
		$res = $this->hash->cron($date);
		
		foreach($res as $key=>$hash){
			if($key >= 1){
				$this->sendGlobal($hash['global'], $hash['date']);
			}
		}
	}

	private function globalToday(){
		$req = $this->db->prepare('SELECT val FROM conf WHERE field=\'last_global\'');
		$req->execute();
		$last_global = $req->fetch(PDO::FETCH_OBJ)->val;
		if($last_global < date('Y-m-d')){
			$dateTime = new \DateTime;
			$dateTime->setTimezone(new \DateTimeZone('America/New_York'));
			$time = $dateTime->format('Gi');
			if((\model\Date::max() > date('Y-m-d') && $time == 600) || (\model\Date::max() <= date('Y-m-d') && $time == 930)){
				$hash = new \view\Hash();
				$data = $hash->getGlobal(date('Y-m-d'))['global'];
				$this->sendGlobal($data, date('Y-m-d'));
			}
		}
	}
	
	private function sendGlobal($data, $date){
		$this->send(
			(date('Y-m-d') === $date ? 'Today\'s globalhash' : 'Globalhash of '.$date)
			. ': '.$data->lat.', '.$data->lng
			. ', '.\model\GeoName::get($data->lat, $data->lng)->geoName
			. ' https://geohashing.info/'.str_replace('-', '', $date).'/global'
		);
	}
}
