<?php

namespace view;

class Dow{
	private $dt_start;
	private $dt_end;
	private $ext;
	private $matches;
	
	public function __construct($url) {
		if(preg_match('/^\/dow(.php)?\/'.\lib\RegExp::date(true,true).\lib\RegExp::ext(true).'$/', $url, $this->matches)){
			if(\lib\RegExp::partExists('d', $this->matches)){
				$this->dt_start = new \DateTime($this->matches['y'].'-'.$this->matches['m'].'-'.$this->matches['d']);
				$this->dt_end = null;
			} elseif (\lib\RegExp::partExists('m', $this->matches)){
				$this->dt_start = new \DateTime($this->matches['y'].'-'.$this->matches['m'].'-01');
				$this->dt_end = new \DateTime($this->matches['y'].'-'.$this->matches['m'].'-'.cal_days_in_month(CAL_GREGORIAN, $this->matches['m'], $this->matches['y']));
			} else {
				$this->dt_start = new \DateTime($this->matches['y'].'-01-01');
				$this->dt_end = new \DateTime($this->matches['y'].'-12-31');
			}
			$this->ext = \lib\RegExp::partExists('ext', $this->matches) ? $this->matches['ext'] : null;
		} else {
			\lib\Error::send(404, 'No valid url, use <u>/dow/&lt;yyyy&gt;[/&lt;mm&gt;[/&lt;dd&gt;]]</u>');
		}
	}
	
	public function get(){
		$dow = \model\Dow::get($this->dt_start, $this->dt_end);
		if($dow === false){
			\lib\Error::send(404, 'This dow is not available');
		} else {
			switch($this->ext){
				case 'json':
					header('Content-Type: application/json');
					echo json_encode($dow);
					break;

				case 'csv':
					header('Content-Type: text/csv');
					echo '"Date","Dow"'."\r\n";
					if(is_array($dow)){
						foreach($dow as $r){
							echo $r->date.','.$r->dow."\r\n";
						}
					} else {
						echo $dow->date.','.$dow->dow."\r\n";
					}
					break;

				default: 
					if(is_array($dow)){
						\lib\Error::send(404,'Display list not implemented');
					} else {
						header('Content-Type: text/plain');
						echo $dow->dow;
					}
			}
		}	
	}
}