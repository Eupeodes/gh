<?php

namespace lib;

class RegExp {
	public static $date_y = '(?P<y>[12][0-9]{3})';
	public static $date_m = '(?P<m>0?[1-9]|1[0-3])';
	public static $date_d = '(?P<d>0?[1-9]|[12][0-9]|3[01])';
	public static $date_s = '(?P<y2>[12][0-9]{3})(?P<m2>0[1-9]|1[0-3])(?P<d2>0[1-9]|[12][0-9]|3[01])';
	
	private static function named($name, $regExp){
		//return '('.$regExp.')';
		return '(?P<'.$name.'>'.$regExp.')';
	}
	/* if month is optional but day not the effect will be that day and month together will be optional */
	public static function date($dayOptional = false, $monthOptional = false){
		return '(' .
				self::$date_y .
				($monthOptional ? '(' : '') .
					'(\/|-)'.self::$date_m .
					($dayOptional ? '(' : '') .
						'(\/|-)'.self::$date_d .
					($dayOptional ? ')?' : '') .
				($monthOptional ? ')?' : '')
			. '|' .
				self::$date_s 
			. ')';
	}
	
	public static function parseDate($matches){
		$y = strlen($matches['y']) > 0 ? $matches['y'] : $matches['y2'];
		$m = strlen($matches['m']) > 0 ? $matches['m'] : $matches['m2'];
		$d = strlen($matches['d']) > 0 ? $matches['d'] : $matches['d2'];
		return array($y,$m,$d);
	}
	
	public static function ext($optional = false){
		return '([.](?P<ext>[a-z]+))' . ($optional ? '?' : '');
	}
	
	public static function zoom(){
		return 'z(?P<zoom>[0|1]?[0-9])';
	}
	
	public static function graticule(){
		$m90 = '[0-8]?[0-9]';
		$m180 = '(1[0-7]|[0]?[0-9])?[0-9]';
		
		return '('.
				self::named('ns1', $m90.'[ns]').self::named('ew1', $m180.'[ew]').'|'.
				self::named('ew2', $m180.'[ew]').self::named('ns2', $m90.'[ns]').'|'.
				self::named('ns3', '[ns]'.$m90).self::named('ew3', '[ew]'.$m180).'|'.
				self::named('ew4', '[ew]'.$m180).self::named('ns4', '[ns]'.$m90).'|'.
				self::named('ns5', '[-]?'.$m90).'(,| |, )'.self::named('ew5', '[-]?'.$m180).'|'.
				'global'.
			')';
		
	}
	
	public static function parseGraticule($matches){
		foreach($matches as $key=>$value){
			$value = strtolower($value);
			if(!is_numeric($key) && $value !== ''){
				if(strpos($key, 'ns') !== false){
					$lat = (strpos($value, 's') !== false ? '-' : '') . intval(str_replace(array('n', 's'), '', $value));
				} elseif(strpos($key, 'ew') !== false){
					$lng = (strpos($value, 'w') !== false ? '-' : '') . intval(str_replace(array('e', 'w'), '', $value));
				}
			}
		}
		return isset($lat) && isset($lng) ? array($lat, $lng) : false;
	}
	
	public static function hash(){
		return '([0123456789bcdefghjkmnpqrstuvwxyz]{4,})';
	}
	
	public static function partExists($part, $matches){
		return (array_key_exists($part, $matches) && strlen($matches[$part]) > 0);
	}
	
	public static function mapType(){
		return '(map|sat|hyb|ter)';
	}
}