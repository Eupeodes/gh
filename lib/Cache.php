<?php

namespace lib;

class Cache {
	public static function permanent() {
		header('Cache-Control: public, max-age=315360000');
	}
	public static function no(){
		header('Cache-Control: no-cache, max-age=0');
	}
	
	public static function time(){
		
	}
	public static function tillDow(){
		$maxDate = \model\Date::max();
		$nextCheck = \model\Date::nextCheck($maxDate);
		header('Cache-Control: public, max-age='.$nextCheck);
	}
}