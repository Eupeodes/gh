<?php

namespace model;

use \lib\Db, \PDO;

class Holiday {
	public static function lastYear(){
		$db = Db::getInstance();
		$req = $db->prepare('SELECT MAX(year) as maxyear FROM dow_holidays');
		$req->execute();
		return $req->fetch(PDO::FETCH_ASSOC)['maxyear'];
	}
	
	public static function isHoliday(DateTime $date){
		$db = Db::getInstance();
		$req = $db->prepare('SELECT * FROM dow_holidays WHERE date=:date');
		$date_formatted = $date->format('Y-m-d');
		$req->bindParam(':date', $date_formatted, PDO::PARAM_STR);
		$req->execute();
		return count($req->fetchAll(PDO::FETCH_ASSOC)) > 0;
	}
	
	public function save($year){
		$db = Db::getInstance();
		$req = $db->prepare('INSERT INTO dow_holidays VALUES (:date, :holiday, :year)');
		foreach($this->calculate($year) as $day=>$date){
			$req->execute([':date'=>date('Y-m-d', $date), ':holiday'=>$day, ':year'=>(int)$year]);
		}
	}
	
	public function calculate($year){
		return [
			'New Year\'s Day'
				=>$this->observedHoliday($year.'-01-01', false),
			'Martin Luther King, Jr. Day'
				=>$this->getFirstAfter($year.'-01-14', 1),
			'Washington\'s Birthday'
				=>$this->getFirstAfter($year.'-02-14', 1),
			'Good Friday'
				=>strtotime('-2 days', easter_date($year)),
			'Memorial Day'
				=>$this->getLastBefore($year.'-06-01', 1),
			'Independence Day'
				=>$this->observedHoliday($year.'-07-04'),
			'Labor Day'
				=>$this->getFirstAfter($year.'-08-31', 1),
			'Thanksgiving Day'
				=>$this->getFirstAfter($year.'-11-21', 4),
			'Christmas'
				=>$this->observedHoliday($year.'-12-25')
		];
	}
	
	private function observedHoliday($date, $saturday = false){
		$dateStamp = strtotime($date);
		if(date('N', $dateStamp) === '6' && $saturday){//saturday
			$dateStamp = strtotime('-1 day', $dateStamp);
		}
		if(date('N', $dateStamp) === '7'){//sunday
			$dateStamp = strtotime('+1 day', $dateStamp);
		}
		return $dateStamp;
	}

	private function getFirstAfter($date, $dayOfWeek){
		$dateStamp = strtotime($date);
		while($dateStamp = strtotime('+1 day', $dateStamp)){
			if((int)date('N', $dateStamp) === $dayOfWeek){
				return $dateStamp;
			}
		}
	}

	private function getLastBefore($date, $dayOfWeek){
		$dateStamp = strtotime($date);
		while($dateStamp = strtotime('-1 day', $dateStamp)){
			if((int)date('N', $dateStamp) === $dayOfWeek){
				return $dateStamp;
			}
		}
	}
}