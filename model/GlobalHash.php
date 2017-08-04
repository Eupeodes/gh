<?php

namespace model;

use \lib\Db;
class GlobalHash{
	public static function save($date, $lat, $lng){
		$db = Db::getInstance();
		$insert = $db->prepare('INSERT INTO global VALUES (:date, ST_SetSRID(ST_MakePoint(:lng, :lat), 4326))');
		$insert->bindParam(':date', $date);
		$insert->bindParam(':lat', $lat);
		$insert->bindParam(':lng', $lng);
		$insert->execute();
	}

	public function getClosest($lat, $lng, $n=1){
		$db = Db::getInstance();
		$q = $db->prepare('SELECT date
			, ST_X(point) lng, ST_Y(point) lat
			, ST_DISTANCEspheroid(point, ST_SetSRID(ST_MakePoint(:lng, :lat),4326),\'SPHEROID["WGS 84",6378137,298.257223563]\')/1000 distance
			, ST_Azimuth(ST_SetSRID(ST_MakePoint(:lng, :lat),4326), point) AS direction
		FROM global 
		ORDER BY ST_DISTANCEspheroid(point, ST_SetSRID(ST_MakePoint(:lng, :lat),4326),\'SPHEROID["WGS 84",6378137,298.257223563]\') LIMIT '.$n);
		$q->bindParam(':lat', $lat);
		$q->bindParam(':lng', $lng);
		$q->execute();
		return $q->fetchAll();
	}
}