<?php
/*
 * file:		maxDate.php
 * author:		Marten Tacoma
 * contents:	get the maximum date
 */

$maxDate = \model\Date::max();
$nextCheck = \model\Date::nextCheck($maxDate);
$output = array('maxDate'=>$maxDate, 'nextCheck'=>$nextCheck);

header('Content-Type: application/json');
echo json_encode($output);