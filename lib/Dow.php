<?php

namespace lib;

class Dow {
	private $db;

	public function __construct($db) {
		$this->db = $db;
	}

	public function get($date) {
		$result = $this->db->get("SELECT dow FROM dow WHERE date='$date'");
		if($this->db->count($result) == 0){
			$this->db->clear($result);
			if($date < date("Y-m-d")){
				$dow = $this->getNew($date);
				if($dow > 0){
					return $dow;
				}
			}
		} else {
			$dow = \lib\db::row($result)->dow;
			return $dow;
		}
		return false;
	}
	private function getNew($date) {
		$url = "http://geo.crox.net/djia/".str_replace("-", "/", $date);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, true);
		$dow = curl_exec($ch);
		curl_close($ch);
		if($dow > 0){
			\lib\Db\store("INSERT INTO dow VALUES ('$date', '$dow')");
		}
		return $dow;
	}
}