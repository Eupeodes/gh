<?php

namespace model;

use \DateTime, \lib\Db, \PDO;

class Dow{
	public $date;
	public $dow;
	
	/* get dow of one day or range of days */
	public static function get(DateTime $date_start, DateTime $date_end = null){
		$db = Db::getInstance();
		$req = $db->prepare('SELECT * FROM dow WHERE date'.(is_null($date_end) ? '=:date_start' : ' BETWEEN :date_start AND :date_end'));
		$date_start_formatted = $date_start->format('Y-m-d');
		$req->bindParam(':date_start', $date_start_formatted, PDO::PARAM_STR);
		if(!is_null($date_end)){
			$date_end_formatted = $date_end->format('Y-m-d');
			$req->bindParam(':date_end', $date_end_formatted, PDO::PARAM_STR);
		}
		$req->execute();
		$res = $req->fetchAll(PDO::FETCH_CLASS, '\model\Dow');
		switch(count($res)){
			case '0':
				$return = false;
				break;
			case '1':
				$return = array_shift($res);
				break;
			default:
				$return = $res;
		}
		return $return;
	}
	
	public static function getNew() {
		$file = explode('/', dirname(__FILE__));
		array_pop($file);
		
		$response = false;
		if(debug_backtrace()[0]['file'] === implode('/', $file).'/cron.php'){
			$db = \lib\Db::getInstance();
			$date = \model\Date::max(false);
			if(\model\Date::nextCheck($date, 0) <= 0){
				$dateTime = new \DateTime($date);
				$req = $db->prepare('INSERT INTO dow VALUES (:date, :dow)');
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, true);
				while(true){
					$url = 'http://geo.crox.net/djia/'.str_replace('-', '/', $date);
					curl_setopt($ch, CURLOPT_URL, $url);
					$dow = curl_exec($ch);
					if(curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200){
						$url = 'http://carabiner.peeron.com/xkcd/map/data/'.str_replace('-', '/', $date);
						curl_setopt($ch, CURLOPT_URL, $url);
						$dow = curl_exec($ch);
					}
					if(curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200){
						$req->execute([':date'=>$date, ':dow'=>$dow]);
						$response = true;
						$dateTime->add(new \DateInterval('P1D'));
						$date = $dateTime->format('Y-m-d');
					} else {
						break;
					}
				}
				curl_close($ch);
			}
		}
		return $response;
	}
}
