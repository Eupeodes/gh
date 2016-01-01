<?php

/*
 * file:		hash.php
 * author:		Marten Tacoma
 * contents:	display a hash
 */

if(preg_match('/^hash(?P<wk>\/wk)?\/'.\lib\RegExp::date().'(?P<graticule>\/'.\lib\RegExp::graticule().')?'.\lib\RegExp::ext(true).'$/i', filter_input(INPUT_GET, 'url'), $matches)){
	$y = $matches['y'];
	$m = $matches['m'];
	$d = $matches['d'];
	$ext = array_key_exists('ext', $matches) ? $matches['ext'] : null;
	if(checkdate($m,$d,$y)){
		if(array_key_exists('graticule', $matches) && $matches['graticule'] !== ''){//if a graticule is given export data as gpx
			if(strlen($matches['wk']) > 0){
				\lib\Error::send(404, 'Week view for GPX is not yet implemented');
			} else {
				$date = $y.'-'.$m.'-'.$d;
				$hash = new \view\Hash($date);
				if($matches['hash'] === '/global'){
					if(is_null($hash->output['global'])){
						\lib\Error::send(404, 'Data not yet available');
					}
					$lat = $hash->output['global']->lat;
					$lng = $hash->output['global']->lng;
					$filename = 'global';
				} else {
					$hashData = ($lng> -30) ? $hash->output['east'] : $hash->output['west'];
					if(is_null($hashData)){
						\lib\Error::send(404, 'Data not yet available');
					}
					list($lat,$lng) = \lib\RegExp::parseGraticule($matches);
					$lat .= substr($hashData->lat,1);
					$lng .= substr($hashData->lng,1);
					$filename = $lat.'_'.$lng;
				}
				header('Content-type: text/xml');
				header('Content-type: application/force-download;charset=utf-8');

				header('Content-Disposition: attachment; filename="geohash_'.$date.'_'.$filename.'.gpx"');
				echo '<?xml version="1.0"?>
<gpx xmlns="http://www.topografix.com/GPX/1/1" version="1.1" creator="geohashing.info">
	<metadata>
		<name>Geohash '.$date.'</name>
	</metadata>
	<wpt lat="'.$lat.'" lon="'.$lng.substr($hashData->lng, 1).'">
		<name>GH '.$date.' '.substr($matches['hash'],1).'</name>
	</wpt>
</gpx>';
			}
		} elseif ($ext === 'gpx'){
			\lib\Error::send(400, 'Gpx only valid if graticule is specified');
		} elseif(strlen($matches['wk']) === 0){
			$hash = new \view\Hash($y.'-'.$m.'-'.$d);
			if(!is_null($hash->output['west'])){
				\lib\Cache::permanent();
			}
			switch($ext){
				case 'json':
					header('Content-Type: application/json');
					echo json_encode($hash->output);
					break;
				default:
					header('Content-Type: text/plain');
					echo 'Date: '.$hash->output['date']."\n"
						. ($hash->output['west'] === null ? 'West not available' : 'Western offset (Non-W30): '.$hash->output['west']->lat.', '.$hash->output['west']->lng)."\n"
						. ($hash->output['east'] === null ? 'East not available' : 'Eastern offset (W30): '.$hash->output['east']->lat.', '.$hash->output['east']->lng)."\n"
						. ($hash->output['global'] === null ? 'Global not available' : 'Global hash: '.$hash->output['global']->lat.', '.$hash->output['global']->lng);
			}
		} else {
			$dateTime = new \DateTime($y.'-'.$m.'-'.$d);
			$output = [];
			$n = 0;
			$int = new \DateInterval('P1D');
			while(true){
				$hash = new \view\Hash($dateTime->format('Y-m-d'));
				if(is_null($hash->output['east'])){
					break;
				}
				$output[] = $hash->output;
				$n++;
				if($n >= 7){
					break;
				}
				$dateTime->add($int);
			}
			if(!is_null($hash->output['west'])){
				\lib\Cache::permanent();
			}
			header('Content-Type: application/json');
			echo json_encode($output);		
		}
	} else {
		\lib\Error::send(400, 'This date is not a valid date');
	}
} else {
	\lib\Error::send(404, 'No valid url');
}
