<?php

namespace model;

use \DateTime;
use \model\Dow;

class Hash{

	private $date;
	public $lat;
	public $lng;
	
	public function __construct(DateTime $date, Dow $dow, $global = false){
		$this->date = $date->format('Y-m-d');
		$md5 = md5($this->date."-".$dow->dow);
		list($lat,$lng) = str_split($md5,16);
		if($global){
			$this->lat = $this->hex2dec($lat) * 180 - 90;
			$this->lng = $this->hex2dec($lng) * 360 - 180;
		} else {
			$this->lat = $this->hex2dec($lat);
			$this->lng = $this->hex2dec($lng);
		}
	}
	
	private function hex2dec($var){
		$o = 0;
		for($i=0;$i<16;$i++){
			$o += hexdec($var[$i])*pow(16,-$i-1);
		}
		return $o;
	}
}
