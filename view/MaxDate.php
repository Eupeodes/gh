<?php

namespace view;

class MaxDate{
	public function view(){
		$maxDate = \model\Date::max();
		$nextCheck = \model\Date::nextCheck($maxDate);
		$output = array('maxDate'=>$maxDate, 'nextCheck'=>$nextCheck);

		header('Content-Type: application/json');
		echo json_encode($output);		
	}
}