<?php

$title = 'Settings';

$settings = [];
$cookie = filter_input(INPUT_COOKIE, 'config');
if(is_null($cookie)){
	$content = 'No cookie found';
} else {
	$cookieData = json_decode($cookie);
	foreach($cookieData as $key=>$val){
		switch($key){
			case 'home':
			case 'center':
			case 'grid':
				$settings[$key] = '['.$val[1].','.$val[0].']';
				break;
			default:
				$settings[$key] = $val;
		}
	}

	$content = '<p>The following settings have been saved</p>'
		. '<table>'
		. '<tr><td>Home</td><td>'.$settings['home'].'</td></tr>'
		. '<tr><td>Map center</td><td>'.$settings['center'].'</td></tr>'
		. '<tr><td>Grid center</td><td>'.$settings['grid'].'</td></tr>'
		. '<tr><td>Zoom</td><td>'.$settings['zoom'].'</td></tr>'
		. '<tr><td>Show controls</td><td>'.($settings['controlsVisible'] ? 'Yes' : 'No').'</td></tr>'
		. '<tr><td>Map type</td><td>'.$settings['type'].'</td></tr>'
		. '<tr><td>Show single day</td><td>'.($settings['single'] ? 'Yes' : 'No').'</td></tr>'
		. '<tr><td>Marker color</td><td>#'.$settings['colorSet'].'</td></tr>'
		. '<tr><td>Marker shows day of</td><td>'.$settings['dayOf'].'</td></tr>'
		. '<tr><td>Map click / redetect action</td><td>'.$settings['setHomeGrid'].'</td></tr>'
		. '<tr><td>Map click action is set to \'Do Nothing\' after click or redetect</td><td>'.($settings['resetSetHomeGrid'] ? 'Yes' : 'No').'</td></tr>'
		. '</table>';
}