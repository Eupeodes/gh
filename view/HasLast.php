<?php

namespace view;

class HasLast{
	public function view(){
		$maxDate = \model\Date::max();
		$nextCheck = \model\Date::nextCheck($maxDate, 0);
		$output = ['status' => $nextCheck > 1 ? 'OK' : 'outdated'];
		header('Content-Type: application/json');
		echo json_encode($output);		
	}
}