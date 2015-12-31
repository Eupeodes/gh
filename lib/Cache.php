<?php

namespace lib;

class Cache {
	public static function permanent() {
		header('Cache-Control: public, max-age=315360000');
	}
	public static function no(){
		header('Cache-Control: no-cache');
	}
	
	public static function time(){
		
	}
}