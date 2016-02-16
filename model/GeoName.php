<?php

namespace model;

class GeoName {
	public $lat;
	public $lng;
	public $geoName;
	
	public function __construct($lat, $lng, $maxLength = 999) {
		$this->lat = $lat;
		$this->lng = $lng;
		if($lat <= -67){
			$output = 'Antarctica';
		} elseif($lat > 84){
			$output = 'North Pole';
		} else {
			$xml = simplexml_load_file('http://api.geonames.org/extendedFindNearby?lat='.$lat.'&lng='.$lng.'&username='.\config::$keys['geonames']);
			$output = '';
			if(isset($xml->ocean)){
				$output = $xml->ocean->name;
			} elseif(isset($xml->address)) {
				$output .= 'United States, '.$xml->address->adminName1.', '.$xml->address->adminName2.(is_string($xml->address->placename) ? ', '.$xml->address->placename : '');
			} else {
				$i = 0;
				$output = '';
				foreach($xml->geoname as $geoname){
					if($i >= 2){
						$newOutput = $output . (($output === '') ? '' : ', ') . $geoname->name;
						if(strlen($newOutput) > $maxLength){
							break;
						} else {
							$output = $newOutput;
						}
					}
					$i++;
				}
			}
		}
		$this->geoName = (string) $output;
	}

	public static function get($lat, $lng, $maxLength = 999){
		$d = new \model\GeoName($lat, $lng, $maxLength);
		return $d;
	}
}