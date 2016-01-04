<!doctype html>
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
				system: {
					minZoom: 1,
					maxZoom: 19,
					minDate: '<?=\model\Date::min()?>',
					maxDate: '<?=$maxDate?>',
					date: '<?=$date?>',
					refreshMaxDate: '<?=\model\Date::nextCheck($maxDate)?>',
					bingKey: '<?=\config::$keys['bing']?>'
				},
				user: {
					home: <?=$settings['home']?>,
					center: <?=$settings['center']?>,
					zoom: <?=$settings['zoom']?>,
					controlsVisible: true,
					type: '<?=$settings['type']?>',
					single: <?=$settings['single'] ? 'true' : 'false'?>,
					colorSet: <?=$settings['colorSet']?>
				}
			};
		</script>
		<?php
		if(array_key_exists('debug', filter_input_array(INPUT_GET))){
		?>
		<link rel="stylesheet" type="text/css" href="/css/style.css" />
		<script type="text/javascript" src="/js/external/jquery.min.js"></script>
		<script type="text/javascript" src="/js/external/jquery-ui.min.js"></script>
		<script type="text/javascript" src="/js/external/jquery.ui.touch-punch.min.js"></script>
		<script type="text/javascript" src="/js/external/ol.js"></script>
		
		<script type="text/javascript" src="/js/script.js"></script>
		<?php
		} else {
		?>
		<link rel="stylesheet" type="text/css" href="/css/css.css?v=<?=file_get_contents('../version')?>" />
		<script type="text/javascript" src="/js/js.js?v=<?=file_get_contents('../version')?>"></script>
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
				<div class="content"><input type="checkbox" id="showWeek" <?=($settings['single']) ? '' : 'checked'?> /><label for="showWeek"> Show up to 6 following days</label></div>
			</div>

			<div class="control" id="zoomControl">
				<div class="title">Zoom <input type="text" name="inputZoomLevel" id="inputZoomLevel" size="2" />
					<div class="sliderParent">
						<div class="slider"></div>
					</div>
				</div>
				<div class="content"><button onclick="zoom.reset();">Reset zoom and center</button> <button onclick="geolocation.setTracking(true);">Redetect home</button></div>
			</div>
			<div class="control" id="mapControl">
				<div class="title">Map</div>
				<ul></ul>
			</div>
			<div class="control" id="markerControl">
				<div class="title">Markers</div>
				<ul>
					<li><input type="radio" name="dayOf" id="dayOfMonth" <?=$settings['dayOf']==='month' ? 'checked' :''?> /><label for="dayOfMonth">Show day of month (1 - 31)</label></li>
					<li><input type="radio" name="dayOf" id="dayOfWeek" <?=$settings['dayOf']==='week' ? 'checked' :''?>/><label for="dayOfWeek">Show day of week (Mo - Su)</label></li>
				</ul>
				<div class="content">
					<?php
					$colors = [
						['f00','fff'],
						['00f','fff'],
						['060','fff'],
						['0f0','000'],
						['0ff','000'],
						['f0f','fff']
					];
					foreach($colors as $key=>$color){
						echo '<div class="colorPicker'.($settings['colorSet'] === $key ? ' selected' :'').'" style="background-color:#'.$color[0].';color:#'.$color[1].'" fgcolor="'.$color[1].'" bgcolor="'.$color[0].'" setid="'.$key.'" id="color_'.$key.'">x</div>';
					}?>
					<div style="clear:both"></div>
				</div>
			</div>
			<div class="version">Geohashing.info <?=file_get_contents('../version')?> - <?=date('Y-m-d', filemtime('../version'))?></div>
	</body>
</html>
