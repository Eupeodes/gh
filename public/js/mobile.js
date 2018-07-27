var maxAccuracy = 100;
var watchL,watchC;

$(function(){
	if ("geolocation" in navigator){
		functional = true;
		Compass.noSupport(function () {
			$('#compass').html('<br/>Compass is not available on your device.<br/><br/>You can use the QR code to open this page on your mobile.<br/><img src="https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=https://geohashing.info/mobile/'+date+'/'+graticule+'&choe=UTF-8" />');
			functional = false;
		});
		
		if(functional){
			initCompass();
		}
	} else {
		$('#compass').html('<br/>Geolocation is not available on your device');
	}
});

function initCompass(){
	Compass.needGPS(function () {
		$('#compass').html('<br/>GPS signal needed to get compass direction');
	}).needMove(function () {
		$('#compass').html('<br/>Please move to get orientation');
	}).init(function () {
		$('#compass').html('<br/><div id="arrow"><div></div></div>');
		getHash();
		watchL = setInterval(doWatch, 1000);
		watchC = Compass.watch(function (angle) {
			deviceHeading = angle;
			setCompass();
		});
		
	});
}

var hash = null;
var loc = null;
var deviceHeading = 0;
var bearing = 0;
var accuracy = 999;

function getHash(){
	$.getJSON(
		'//data.geohashing.info/hash/'+date+'/'+graticule+'.json',
		function(json){
			hash = LatLon(json.lat, json.lng);
		}
	);
	
}

function getLocation(){
	var options = {
		enableHighAccuracy: true,
		timeout: 5000,
		maximumAge: 0
	};
	navigator.geolocation.getCurrentPosition(function(position){
		loc = LatLon(position.coords.latitude, position.coords.longitude);
		$('#accuracy').text(position.coords.accuracy + ' m');
		accuracy = position.coords.accuracy.toFixed(0);
		if(accuracy > maxAccuracy){
			$('#accuracy').addClass('low');
		} else {
			$('#accuracy').removeClass('low');
		}
	},function(){},options);
}

function setCompass(){
	heading = 360-deviceHeading+bearing;
	$('#arrow').css('-moz-transform', 'rotate('+heading+'deg)');
	$('#arrow').css('-webkit-transform', 'rotate('+heading+'deg)');
	$('#arrow').css('-o-transform', 'rotate('+heading+'deg)');
	$('#arrow').css('-ms-transform', 'rotate('+heading+'deg)');
}

function doWatch(){
	getLocation();
	if(hash !== null && loc !== null){
		$('#compass').show();
		dist = loc.distanceTo(hash);
		unit = 'm';
		if(dist > 9999){
			dist = dist/1000;
			unit = 'km';
		}
		$('#distance').text(dist.toFixed(2)+ ' ' + unit);
		bearing = loc.bearingTo(hash);

		if(dist < accuracy && accuracy < maxAccuracy){
			$('#compass').addClass('reached');
			$('#status').show();
			var now = new Date();
			$('#status').html('<h2>Congratulations!</h2><p>You have reached the<br/><br/><strong>'+date+' '+graticule+'</strong><br/><br/> hash on</p><p>'+now.toLocaleString()+'</p>');
		} else {
			$('#compass').removeClass('reached');
			$('#status').hide();
		}
	}
	
}