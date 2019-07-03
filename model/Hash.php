<?php

namespace model;

use \DateTime;
use \model\Dow;

class Hash{

	private $date;
	public $lat;
	public $lng;
	private $decimals = 5;
	
	public function __construct(DateTime $date, Dow $dow, $global = false){
		$this->date = $date->format('Y-m-d');
		$md5 = md5($this->date."-".$dow->dow);
		list($lat,$lng) = str_split($md5,16);
		if($global){
			$this->lat = round($this->hex2dec($lat) * 180 - 90, $this->decimals);
			$this->lng = round($this->hex2dec($lng) * 360 - 180, $this->decimals);
		} else {
			$this->lat = round($this->hex2dec($lat), $this->decimals);
			$this->lng = round($this->hex2dec($lng), $this->decimals);
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
