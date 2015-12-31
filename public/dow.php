<?php
/*
 * file:		dow.php
 * author:		Marten Tacoma
 * contents:	display one or more dows
 */
if(preg_match('/^dow\/'.\lib\RegExp::date(true,true).\lib\RegExp::ext(true).'$/', filter_input(INPUT_GET, 'url'), $matches)){
	if(array_key_exists('d', $matches) && is_numeric($matches['d'])){
		$dt_start = new DateTime($matches['y'].'-'.$matches['m'].'-'.$matches['d']);
		$dt_end = null;
	} elseif (array_key_exists('m', $matches) && is_numeric($matches['m'])){
		$dt_start = new DateTime($matches['y'].'-'.$matches['m'].'-01');
		$dt_end = new DateTime($matches['y'].'-'.$matches['m'].'-'.cal_days_in_month(CAL_GREGORIAN, $matches['m'], $matches['y']));
	} else {
		$dt_start = new DateTime($matches['y'].'-01-01');
		$dt_end = new DateTime($matches['y'].'-12-31');
	}
	
	$dow = \model\Dow::get($dt_start, $dt_end);
	if($dow === false){
		http_response_code(404);
	} else {
		\lib\Cache::permanent();
		$ext = array_key_exists('ext', $matches) ? $matches['ext'] : null;
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
} else {
	\lib\Error::send(404, 'No valid url, use <u>/dow/&lt;yyyy&gt;[/&lt;mm&gt;[/&lt;dd&gt;]]</u>');
}
