<?php

namespace view;

class Dow{
	public function view($url) {
		if(preg_match('/^\/dow(.php)?\/'.\lib\RegExp::date(true,true).\lib\RegExp::ext(true).'$/', $url, $matches)){
			if(\lib\RegExp::partExists('d', $matches)){
				$dt_start = new \DateTime($matches['y'].'-'.$matches['m'].'-'.$matches['d']);
				$dt_end = null;
			} elseif (\lib\RegExp::partExists('m', $matches)){
				$dt_start = new \DateTime($matches['y'].'-'.$matches['m'].'-01');
				$dt_end = new \DateTime($matches['y'].'-'.$matches['m'].'-'.cal_days_in_month(CAL_GREGORIAN, $matches['m'], $matches['y']));
			} else {
				$dt_start = new \DateTime($matches['y'].'-01-01');
				$dt_end = new \DateTime($matches['y'].'-12-31');
			}
			$ext = \lib\RegExp::partExists('ext', $matches) ? $matches['ext'] : null;
			$dow = \model\Dow::get($dt_start, $dt_end);
			if($dow === false){
				\lib\Error::send(404, 'This dow is not available');
			} else {
				$this->display($dow, $ext);
			}
		} else {
			\lib\Error::send(404, 'No valid url, use <u>/dow/&lt;yyyy&gt;[/&lt;mm&gt;[/&lt;dd&gt;]]</u>');
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
				if(is_array($dow)){
					\lib\Error::send(404,'Display list not implemented');
				} else {
					header('Content-Type: text/plain');
					echo $dow->dow;
				}
		}
	}
}
