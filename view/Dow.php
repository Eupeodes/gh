<?php

namespace view;

class Dow{
	private $dateLimits;
	private $url;

	public function __construct(){
		$this->dateLimits = ['min'=>\model\Date::min(), 'max'=>\model\Date::max()];
	}

	public function view($url) {
		$this->url = $url;
		if(preg_match('/^\/dow(\/'.\lib\RegExp::date(true,true).')?'.\lib\RegExp::ext(true).'$/', $url, $matches)){
			if(\lib\RegExp::partExists('d', $matches)){
				if(checkdate($matches['m'], $matches['d'], $matches['y'])){
					$dt_start = new \DateTime($matches['y'].'-'.$matches['m'].'-'.$matches['d']);
					$dt_end = $dt_start;
				} else {
					\lib\Error::send(404, 'Invalid date');
				}
			} elseif (\lib\RegExp::partExists('m', $matches)){
				if(checkdate($matches['m'], 1, $matches['y'])){
					$dt_start = new \DateTime($matches['y'].'-'.$matches['m'].'-01');
					$dt_end = new \DateTime($matches['y'].'-'.$matches['m'].'-'.cal_days_in_month(CAL_GREGORIAN, $matches['m'], $matches['y']));
				} else {
					\lib\Error::send(404, 'Invalid month');
				}
			} elseif (\lib\RegExp::partExists('y', $matches)) {
				$dt_start = new \DateTime($matches['y'].'-01-01');
				$dt_end = new \DateTime($matches['y'].'-12-31');
			} else {
				$dt_start = new \DateTime($this->dateLimits['min']);
				$dt_end = new \DateTime($this->dateLimits['max']);
			}
			if($dt_start >= new \DateTime($this->dateLimits['max'])){
				\lib\Error::send(404, 'This dow is not yet available');
			} elseif($dt_end < new \DateTime($this->dateLimits['min'])){
				\lib\Error::send(404, 'Dow\'s before '.$this->dateLimits['min'].' are not available');
			} else {
				$ext = \lib\RegExp::partExists('ext', $matches) ? $matches['ext'] : null;
				if($ext === null && $dt_start <> $dt_end){
					$ext = 'json';
				}
				$dow = \model\Dow::get($dt_start, $dt_end);
				if($dow === false){
					\lib\Error::send(404, 'This dow is not available');
				} else {
					$this->display($dow, $ext);
				}
			}
		} else {
			\lib\Error::send(400, 'No valid url, use <u>/dow/[&lt;yyyy&gt;[/&lt;mm&gt;[/&lt;dd&gt;]][.(csv|json)]]</u>');
		}
	}
	
	private function display($dow, $ext){
		switch($ext){
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
				header('Content-Type: text/plain');
				echo $dow->dow;
		}
	}
}
