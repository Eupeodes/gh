<?php

namespace model;

use \lib\Db, \PDO;

class Date{
	public $value;
	
	public static function min(){
		$res = self::get('SELECT min(date) AS value FROM dow');
		return array_shift($res)->value;
	}
	
	public static function max(){
		$res = self::get($query = 'SELECT max(date) AS value FROM dow');
		$dt = new \DateTime(array_shift($res)->value);
		$dt->add(new \DateInterval('P1D'));
		return $dt->format('Y-m-d');
	}
	
	private static function get($query){
		$db = Db::getInstance();
		$req = $db->prepare($query);
		$req->execute();
		return $req->fetchAll(PDO::FETCH_CLASS, '\model\Date');
	}
	
	public static function nextCheck($maxDate){
		$next = new \DateTime($maxDate.' 9:30', new \DateTimeZone('America/New_York'));
		$diff = $next->getTimestamp()-time()+10;
		return $diff < 60 ? 60 : $diff;
	}
}
