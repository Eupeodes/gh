<?php
/*
 * file:		index.php
 * author:		Marten Tacoma
 * contents:	the main page
 */

$settings = [
	'home'=>'[0,0]',
	'center'=>'[0,0]',
	'single'=>false,
	'zoom'=>8,
	'type'=>'map',
	'dayOf'=>'month',
	'colorSet'=>4
];

$maxDate = \model\Date::max();
$date = date('Y-m-d');

$set = false;
$params = array('date', 'lng', 'lon', 'multi', 'zoom', 'type');
foreach($params as $param){
	if(array_key_exists($param, filter_input_array(INPUT_GET))){
		switch($param){
			case 'multi':
				$settings['single'] = filter_input(INPUT_GET, 'multi') !== 'false';
				break;
			case 'lng':
				$settings['home'] = '['.filter_input(INPUT_GET, 'lng').','.filter_input(INPUT_GET, 'lat').']';
				break;
			case 'lon':
				$settings['home'] = '['.filter_input(INPUT_GET, 'lon').','.filter_input(INPUT_GET, 'lat').']';
				break;
			default:
				$settings[$param] = filter_input(INPUT_GET, $param);
		}
		$set = true;
	}
}
if(!$set){
	if(preg_match('/^(\/)?((?P<type>'.\lib\RegExp::mapType().')\/)?(?P<date>'.\lib\RegExp::date().'(\/(?P<single>s(ingle)?))?(\/(?P<home>'.\lib\RegExp::hash().')(\/(?P<zoom>[[0|1]?[0-9])(\/(?P<center>'.\lib\RegExp::hash().'))?)?)?)?(\/)?$/i', filter_input(INPUT_GET, 'url'), $matches)){
		if(\lib\RegExp::partExists('type', $matches)){
			$settings['type'] = $matches['type'];
		}
		if(\lib\RegExp::partExists('date', $matches)){
			\lib\RegExp::parseDate($matches);
			list($y,$m,$d) = \lib\RegExp::parseDate($matches);
			if(checkdate($m,$d,$y) && $y.'-'.$m.'-'.$d <= $maxDate){
				$date = $y.'-'.$m.'-'.$d;
			}
		}
		if(\lib\RegExp::partExists('home', $matches)){
			$geohash = new \lib\external\GeoHash();
			list($lat, $lng) = $geohash->decode($matches['home']);
			$settings['home'] = '['.$lng.', '.$lat.']';
		}
		if(\lib\RegExp::partExists('zoom', $matches)){
			$settings['zoom'] = $matches['zoom'];
		}
		if(\lib\RegExp::partExists('single', $matches)){
			$settings['single'] = true;
		}
		//center is currently not being used
	} else {
		if(preg_match('/(\/|^)(?P<type>'. \lib\RegExp::mapType().')(\/|$)/i', filter_input(INPUT_GET, 'url'), $matches)){
			$settings['type'] = $matches['type'];
		}
		if(preg_match('/(\/|^)'. \lib\RegExp::date().'(\/|$)/i', filter_input(INPUT_GET, 'url'), $matches)){
			list($y,$m,$d) = \lib\RegExp::parseDate($matches);
			if(checkdate($m,$d,$y) && $y.'-'.$m.'-'.$d <= $maxDate){
				$date = $y.'-'.$m.'-'.$d;
			}
		}
		if(preg_match('/(\/|^)(?P<graticule>'.\lib\RegExp::graticule().')(\/|$)/i', filter_input(INPUT_GET, 'url'), $matches)){
			if($matches['graticule'] === 'global'){
				$hash = new \view\Hash($date);
				$settings['home'] = '['.$hash->getInt($hash->output['global']->lng).'.5,'.$hash->getInt($hash->output['global']->lat).'.5]';
			} else {
				list($lat, $lng) = \lib\RegExp::parseGraticule($matches);
				$settings['home'] = '['.$lng.'.5, '.$lat.'.5]';
			}
		}

		if(preg_match('/(\/|^)s(ingle)?(\/|$)/i', filter_input(INPUT_GET, 'url'), $matches)){
			$settings['single'] = true;
		}

		if(preg_match('/(\/|^)'.\lib\RegExp::zoom().'(\/|$)/i', filter_input(INPUT_GET, 'url'), $matches)){
			$settings['zoom'] = $matches['zoom'];
		}
	}
}

$settings['center'] = $settings['home'];

include('../template/index.php');
?>
