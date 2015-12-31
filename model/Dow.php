<?php

namespace model;

use \DateTime, \lib\Db, \PDO;

class Dow{
	public $date;
	public $dow;
	
	/* get dow of one day or range of days */
	public static function get(DateTime $date_start, DateTime $date_end = null){
		$db = Db::getInstance();
		$query = 'SELECT * FROM dow WHERE date'.(is_null($date_end) ? '=:date_start' : ' BETWEEN :date_start AND :date_end');
		$req = $db->prepare($query);
		$req->bindParam(':date_start', $date_start->format('Y-m-d'), PDO::PARAM_STR);
		if(!is_null($date_end)){
			$req->bindParam(':date_end', $date_end->format('Y-m-d'), PDO::PARAM_STR);
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
}
