<?php

namespace view;

class HasLast{
	public function view(){
		$maxDate = \model\Date::max();
        $nextCheck = \model\Date::nextCheck($maxDate, null);
        $status = $nextCheck > 0 ? 'OK' : ($nextCheck > -60 ? 'Pending' : 'Outdated');
		$output = ['status' => $status, 'diff' => $nextCheck];
		header('Content-Type: application/json');
		echo json_encode($output);		
	}
}
