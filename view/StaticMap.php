<?php

namespace view;

class StaticMap {
	protected $cacheFile;
	
	public function view(){
		
	}
	
	public function get($date, $graticule, $mapType = 'roadmap'){
		$this->cacheFile = str_replace('-', '', $date).'_'.str_replace(',', '_', $graticule).'_'.$mapType.'.png';
	}
}
ini_set("display_errors", "on");
$file = $_SERVER['DOCUMENT_ROOT']."/static_cache/";

if(isset($_GET['global'])){
	$file .= "global";
} elseif (is_numeric($_GET['lat']) && is_numeric($_GET['lng']) && strpos($_GET['lat'], ".") === false && strpos($_GET['lng'], ".") === false && $_GET['lat'] >= -90 && $_GET['lat'] <= 90 && $_GET['lng'] >= -180 && $_GET['lng'] <= 180){
	$file .= "hash_".$_GET['lat']."_".$_GET['lng'];
} else {
	die('invalid input');
}
list($y,$m, $d) = explode("-", $_GET['date']);
if(!is_numeric($d) || !is_numeric($m) || !is_numeric($y) || !checkdate($m, $d, $y)){
	die("invalid date");
} else {
	$file .= "_$y$m$d";
}
$file .= ".png";
if(!file_exists($file)){
	$c = new getCoordinates();

	$r = $c->calcCoords($_GET['date']);

	$available = false;

	if(isset($_GET['global']) && isset($r['result']['G']['lat'])){
		$lat = round($r['result']['G']['lat'] * 180 - 90, 5);
		$lng = round($r['result']['G']['lng'] * 360 - 180, 5);
		$zoom = 5;
		$available = true;
	} else {
		if($_GET['lat'] > -30 && isset($r['result']['E']['lat'])){
			$dlat = round($r['result']['E']['lat'], 5);
			$dlng = round($r['result']['E']['lng'], 5);
			$available = true;
		} elseif ($_GET['lat'] <= -30 && isset($r['result']['W']['lat'])){
			$dlat = round($r['result']['W']['lat'], 5);
			$dlng = round($r['result']['W']['lng'], 5);
			$available = true;
		}
		if($available){
			$lat = $_GET['lat'].substr($dlat, 1);
			$lng = $_GET['lng'].substr($dlng, 1);
			if($_GET['lat'] < 60) {
				$zoom = 8;
			} elseif ($_GET['lat'] < 75) {
				$zoom = 7;
			} elseif ($_GET['lat'] < 82) {
				$zoom = 6;
			} elseif ($_GET['lat'] < 86) {
				$zoom = 5;
			} elseif ($_GET['lat'] < 88) {
				$zoom = 4;
			} elseif($_GET['lat'] >= 88) {
				$zoom = 3;
			}
		}
	}

	if($available){
		$clat = intval($lat).".5";
		$clng = intval($lng).".5";
		$url = "http://maps.googleapis.com/maps/api/staticmap?center=$clat,$clng";
		$url .= "&size=300x400";
		$url .= "&sensor=false";
		$url .= "&zoom=$zoom";
		$url .= "&path=color:0xff0000ff|weight:3|".floor($lat).",".floor($lng)."|".ceil($lat).",".floor($lng)."|".ceil($lat).",".ceil($lng)."|".floor($lat).",".ceil($lng)."|".floor($lat).",".floor($lng);
		$url .= "&markers=color:blue%7C$lat,$lng";
		$url .= "&key=AIzaSyDZpNi_G0_KqacSGUWW6a76EvIZgvFNiVk";
		if($_GET['type'] === 'sat'){
			$url .= '&maptype=satellite';
		}

		$img = file_get_contents($url);

		file_put_contents($file, $img);
	} else {
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		header('Content-Type: image/png');
		readfile("img/fail.png");
		die();
	}
} else {
	$img = file_get_contents($file);
}

// Getting headers sent by the client.
$headers = apache_request_headers();

// Checking if the client is validating his cache and if it is current.
if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) == filemtime($file))) {
	// Client's cache IS current, so we just respond '304 Not Modified'.
	header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($file)).' GMT', true, 304);
} else {
	// Image not cached or cache outdated, we respond '200 OK' and output the image.
	header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($file)).' GMT', true, 200);
	header('Content-Length: '.filesize($file));
	header('Content-Type: image/png');
	echo $img;
}
?>