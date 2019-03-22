<!doctype html>
<html>
	<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
			<meta http-equiv="X-UA-Compatible" content="IE=Edge">
			<meta name="author" content="Marten Tacoma, 2015">
			<meta name="application-name" content="Geohashing.info">
			<meta name="keywords" content="geohashing, gps, xkcd">
			<meta name="description" content="A calculator for geohashing">
			<meta name="page-version" content="<?=$this->version?>">
			<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
			<meta name="apple-mobile-web-app-capable" content="yes">
			<meta name="apple-mobile-web-app-status-bar-style" content="black">
			<link rel="icon" type="image/png" href="/img/icon.png" />
			<link rel="apple-touch-icon" type="image/png" href="/img/icon.png" />
			<title>Geohashing.info</title>
			<link rel="stylesheet" type="text/css" href="/css/mobile.min.css?v=<?=$this->version?>" />
			<script type="text/javascript" src="/js/external/jquery.min.js"></script>
			<script type="text/javascript" src="/js/external/compass.js"></script>
			<script type="text/javascript" src="/js/external/gps.js"></script>
			<script type="text/javascript">date='<?=$date?>';graticule='<?=$graticule ?? 'global'?>';</script>
			<script type="text/javascript" src="/js/gh/mobile.min.js?v=<?=$this->version?>"></script>

	</head>
	<body>
	<div id="distance">--</div>
	<div id="accuracy">--</div>
	<div id="status"></div>
	<div id="compass"></div>
	<div id="date"><?=$date?></div>
	<div id="location"><?=$graticule ?? 'global'?></div>
	</body>
</html>
