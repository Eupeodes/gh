<?php
/*
 * file:		index.php
 * author:		Marten Tacoma
 * contents:	the main page
 */

$maxDate = \model\Date::max();

$date = date('Y-m-d');
$center = '[0,0]';
$single = false;
$zoom = 8;
$type = 'map';

$set = false;
$params = array('date', 'lng', 'lon', 'multi', 'zoom', 'type');
foreach($params as $param){
	if(array_key_exists($param, filter_input_array(INPUT_GET))){
		switch($param){
			case 'multi':
				$single = filter_input(INPUT_GET, 'multi') !== 'false';
				break;
			case 'lng':
				$center = '['.filter_input(INPUT_GET, 'lng').','.filter_input(INPUT_GET, 'lat').']';
				break;
			case 'lon':
				$center = '['.filter_input(INPUT_GET, 'lon').','.filter_input(INPUT_GET, 'lat').']';
				break;
			default:
				$$param = filter_input(INPUT_GET, $param);
		}
		$set = true;
	}
}
if(!$set){
	if(preg_match('/^(\/)?((?P<type>'.\lib\RegExp::mapType().')\/)?(?P<date>'.\lib\RegExp::date().'(\/(?P<single>s(ingle)?))?(\/(?P<hash>'.\lib\RegExp::hash().')(\/(?P<zoom>[[0|1]?[0-9])(\/(?P<center>'.\lib\RegExp::hash().'))?)?)?)?(\/)?$/i', filter_input(INPUT_GET, 'url'), $matches)){
		if(\lib\RegExp::partExists('type', $matches)){
			$type = $matches['type'];
		}
		if(\lib\RegExp::partExists('date', $matches)){
			\lib\RegExp::parseDate($matches);
			list($y,$m,$d) = \lib\RegExp::parseDate($matches);
			if(checkdate($m,$d,$y) && $y.'-'.$m.'-'.$d <= $maxDate){
				$date = $y.'-'.$m.'-'.$d;
			}
		}
		if(\lib\RegExp::partExists('hash', $matches)){
			$geohash = new \lib\external\GeoHash();
			list($lat, $lng) = $geohash->decode($matches['hash']);
			$center = '['.$lng.', '.$lat.']';
		}
		if(\lib\RegExp::partExists('zoom', $matches)){
			$zoom = $matches['zoom'];
		}
		if(\lib\RegExp::partExists('single', $matches)){
			$single = true;
		}
		//type and center are currently not being used
	} else {
		if(preg_match('/(\/|^)(?P<type>'. \lib\RegExp::mapType().')(\/|$)/i', filter_input(INPUT_GET, 'url'), $matches)){
			$type = $matches['type'];
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
				$center = '['.$hash->getInt($hash->output['global']->lng).'.5,'.$hash->getInt($hash->output['global']->lat).'.5]';
			} else {
				list($lat, $lng) = \lib\RegExp::parseGraticule($matches);
				$center = '['.$lng.'.5, '.$lat.'.5]';
			}
		}

		if(preg_match('/(\/|^)s(ingle)?(\/|$)/i', filter_input(INPUT_GET, 'url'), $matches)){
			$single = true;
		}

		if(preg_match('/(\/|^)'.\lib\RegExp::zoom().'(\/|$)/i', filter_input(INPUT_GET, 'url'), $matches)){
			$zoom = $matches['zoom'];
		}
	}
}

?><!doctype html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=Edge">
		<meta name="author" content="Marten Tacoma, 2015">
		<meta name="application-name" content="Geohashing.info">
		<meta name="keywords" content="geohashing, gps, xkcd">
		<meta name="description" content="A calculator for geohashing">
		<meta name="page-version" content="<?=$version?>">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-status-bar-style" content="black">
		<link rel="icon" type="image/png" href="img/icon.png" />
        <link rel="apple-touch-icon" type="image/png" href="img/icon.png" />
		<title>Geohashing.info</title>
		
		<script type="text/javascript">
			var settings = {
				center: <?=$center?>,
				zoom: <?=$zoom?>,
				minZoom: 1,
				maxZoom: 19,
				date: '<?=$date?>',
				minDate: '<?=\model\Date::min()?>',
				maxDate: '<?=$maxDate?>',
				refreshMaxDate: '<?=\model\Date::nextCheck($maxDate)?>',
				controlsVisible: true,
				type: '<?=$type?>'
			};
		</script>
		<?php
		if(array_key_exists('debug', filter_input_array(INPUT_GET))){
		?>
		<script type="text/javascript" src="/js/external/jquery.min.js"></script>
		<script type="text/javascript" src="/js/external/jquery-ui.min.js"></script>
		<script type="text/javascript" src="/js/external/jquery.ui.touch-punch.min.js"></script>
		<script type="text/javascript" src="/js/external/ol.js"></script>
		
		<script type="text/javascript" src="/js/script.js"></script>
		<link rel="stylesheet" type="text/css" href="/css/style.css" />
		<?php
		} else {
		?>
		<script type="text/javascript" src="/js/js.js?v=<?=file_get_contents('../version')?>"></script>
		<link rel="stylesheet" type="text/css" href="/css/css.css?v=<?=file_get_contents('../version')?>" />
		<?php
		}
		?>
	</head>
	<body>
		<div id="greybox">
			<div id="infobox"></div>
			<div id="helpbox"></div>
		</div>
		<div id="map">
			<div id="popup" class="ol-popup">
				<a href="#" id="popup-closer" class="ol-popup-closer"></a>
				<div id="popup-content"></div>
			</div>
		</div>
		<div id="controlsTop">
			<div id="hamburger" onclick="controls.toggle();">
				<div></div>
				<div></div>
				<div></div>
			</div>
			<div class="title">Geohashing.info</div>
		</div>
		<div id="controls">
			<div class="control" id="dateControl">
				<div class="title">Date <input type="text" id="datepicker" name="inputDate" size="10" /> <span class="hint">(yyyy-mm-dd)</a></div>
				<div class="content datepicker"></div>
				<div class="content"><input type="checkbox" id="showWeek" <?=($single) ? '' : 'checked'?> /><label for="showWeek"> Show up to 6 following days</label></div>
			</div>

			<div class="control" id="zoomControl">
				<div class="title">Zoom <input type="text" name="inputZoomLevel" id="inputZoomLevel" size="2" />
					<div style="float: right;width:230px;margin-top:9px">
						<div class="slider"></div>
					</div>
				</div>
				<div class="content"><button onclick="zoom.reset();">Reset zoom and center</button> <button onclick="geolocation.setTracking(true);">Redetect home</button></div>
			</div>
			<div class="control" id="mapControl">
				<div class="title">Map</div>
				<ul></ul>
			</div>
			<div class="version">Geohashing.info <?=file_get_contents('../version')?> - <?=date('Y-m-d', filemtime('../version'))?></div>
	</body>
</html>
