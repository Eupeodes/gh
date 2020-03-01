<!doctype html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=Edge">
		<meta name="author" content="Marten Tacoma, 2017">
		<meta name="application-name" content="Geohashing.info">
		<meta name="keywords" content="geohashing, gps, xkcd">
		<meta name="description" content="A calculator for geohashing">
		<meta name="page-version" content="<?=$this->version?>">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-status-bar-style" content="black">
		<link rel="icon" type="image/png" href="/img/icon.png" />
		<link rel="apple-touch-icon" type="image/png" href="/img/icon.png" />
		<title>Globalhash history</title>
		<script type="text/javascript">
			var settings = {
				system: {
					minZoom: 1,
					maxZoom: 19,
					bingKey: '<?=\config::$keys['bing']?>'
				},
				user: {
					home: <?=$this->settings['home']?>,
					center: <?=$this->settings['home']?>,
					controlsVisible: <?=$this->settings['controlsVisible'] ? 'true' : 'false'?>,
					type: '<?=$this->settings['type']?>',
					zoom: 8
				}
			};
		</script>
		<link rel="stylesheet" type="text/css" href="/css/style.css?v=<?=$this->version?>" />
		<script type="text/javascript" src="/js/external/jquery.min.js"></script>
		<script type="text/javascript" src="/js/external/jquery-ui.min.js"></script>
		<script type="text/javascript" src="/js/external/jquery.nicescroll.min.js"></script>
		<script type="text/javascript" src="/js/external/jquery.ui.touch-punch.min.js"></script>
		<script type="text/javascript" src="/js/external/ol.js"></script>
		
		<script type="text/javascript" src="/js/base.min.js?v=<?=$this->version?>"></script>
		<script type="text/javascript" src="/js/global.min.js?v=<?=$this->version?>"></script>
	</head>
	<body class="global">
		<div id="map">
			<div id="popup" class="ol-popup">
				<a href="#" id="popup-closer" class="ol-popup-closer"></a>
				<div id="popup-content"></div>
			</div>
		</div>
		<div id="controlsTop">
			<div class="buttons">
				<img src="/img/settings.png" id="toggleControls" /><img src="/img/help.png" id="openHelp"/>
			</div>
			<div class="title">Geohashing.info</div>
		</div>
		<div id="controls">
			<div class="control" id="mapControl">
				<div class="title">Map</div>
				<ul></ul>
			</div>
			<div class="control" id="zoomControl">
				<div class="title">Zoom <input type="text" name="inputZoomLevel" id="inputZoomLevel" size="2" />
					<div class="sliderParent">
						<div class="slider"></div>
					</div>
				</div>
				<div class="content"><button onclick="zoom.reset();">Reset zoom and center</button> <button id="redetectHome">Redetect</button></div>
			</div>
			<div class="control" id="homeControl">
				<div class="title">Map click / redetect action</div>
				<ul>
					<li><input type="radio" name="setHomeGrid" value="nothing" id="setHomeGridNothing" <?=$this->settings['home'] !== '[0,0]' ? 'checked' : ''?> /><label for="setHomeGridNothing">Do nothing (click)</label></li>
					<li><input type="radio" name="setHomeGrid" value="both" id="setHomeGridBoth" <?=$this->settings['home'] === '[0,0]' && $this->settings['home'] === $this->settings['grid'] ? 'checked' : ''?> /><label for="setHomeGridBoth">Move home and center of grid</label></li>
					<li><input type="radio" name="setHomeGrid" value="home" id="setHomeGridHome" <?=$this->settings['home'] === '[0,0]' && $this->settings['home'] !== $this->settings['grid'] ? 'checked' : ''?> /><label for="setHomeGridHome">Only move home</label></li>
					<li><input type="radio" name="setHomeGrid" value="grid" id="setHomeGridGrid" /><label for="setHomeGridGrid">Only move center of grid</label></li>
					<li><input type="checkbox" id="resetSetHomeGrid" <?=($this->settings['resetSetHomeGrid']) ? 'checked' : ''?> /><label for="resetSetHomeGrid">Set to 'Do Nothing' after click or redetect</label></li>
				</ul>
			</div>
			<div class="control" id="markerControl">
				<div class="title">Markers</div>
				<div class="content">
					<?php
					$colors = [
						'f00'=>['f00','fff'],
						'00f'=>['00f','fff'],
						'060'=>['060','fff'],
						'0f0'=>['0f0','000'],
						'0ff'=>['0ff','000'],
						'f0f'=>['f0f','fff']
					];
					foreach($colors as $key=>$color){
						echo '<div class="colorPicker'.($this->settings['colorSet'] === $key ? ' selected' :'').'" style="background-color:#'.$color[0].';color:#'.$color[1].'" fgcolor="'.$color[1].'" bgcolor="'.$color[0].'" setid="'.$key.'" id="color_'.$key.'">x</div>';
					}?>
					<div style="clear:both"></div>
				</div>
			</div>
			<div class="control" id="unitControl">
				<div class="title">Distance unit</div>
				<ul>
					<li><input type="radio" name="unit" id="unitKm" <?=$this->settings['unit']==='km' ? 'checked' :''?> /><label for="unitKm">Kilometers</label></li>
					<li><input type="radio" name="unit" id="unitMi" <?=$this->settings['unit']==='miles' ? 'checked' :''?>/><label for="unitMi">Miles</label></li>
				</ul>
			</div>
			<div>
				<?=$list?>
			</div>

			<div class="control" id="twitterControl">
				<div class="content"><a href="https://twitter.com/geohashing" title="Follow @geohashing on twitter"><img src="/img/twitter.png" />@geohashing</a></div>
			</div>
			<div class="version">Geohashing.info <a href="#" id="openChangelog">v<?=$this->version?></a> - <?=date('Y-m-d', filemtime('../version'))?> | <a href="#" id="openDisclaimer">Disclaimer</a></div>
		</div-->
		<?php if(DEBUG){ ?>
		<div id="w30warning"><div class="close">x</div>Because of <a href="http://geohashing.site/geohashing/30W_Time_Zone_Rule">the 30W rule</a> coordinates west of 30&deg; west are not yet available for <span class="date">the last date</span>. Coordinates east of 30&deg; west and the global hash are available for <span class="date">that date</span>.</div>
		<?php } ?>
	</body>
</html>
