<?php

namespace view;

class Statbar{
	public function view($url){
		ini_set('display_errors', 'on');
		$parts = explode('/', $url);
		$user = urldecode($parts[2]);
		$text = urldecode($parts[3]);
		$file = '../cache/'.urlencode($user.$text).".png";

		header("Content-type: image/png");
		header("Cache-Control: max-age=3600"); // HTTP/1.1

		if(!file_exists($file) || filemtime($file) < time()-86400){
			$base_url = 'http://geohashing.site/geohashing/User:';
			$url = $base_url.$user;
			$data = file_get_contents($url);
			$success = substr_count($data, 'alt="Arrow2.png"');
			$fail = substr_count($data, 'alt="Arrow4.png"');
			$count = $success+$fail;
			$img = imagecreatetruecolor(200,50);
			$img2 = imagecreatefrompng('../public/img/icon.png');
			$bg_color = imagecolorallocate($img, 255,255,255);
			imagefilledrectangle($img,1,1,198,48,$bg_color);
			imagecopyresampled($img, $img2, 1,0,0,0,50,49,135,135);
			$text_color = imagecolorallocate($img, 0,0,0);
			imagestring($img, 3, 55, 3, $text, $text_color);
			imagestring($img, 2, 55, 18, "Attempts: ".$count, $text_color);
			imagestring($img, 2, 55, 32, "Successes: ".$success, $text_color);
			imagepng($img, $file);
		}
		echo file_get_contents($file);
	}
}