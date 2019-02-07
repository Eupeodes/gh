/*
 * file:		script.js
 * author:		Marten Tacoma
 * contents:	all custom js
 */

var map, view, settings, ol, xs,xt,xl, ys,yt,yl, gridLayer, gridVector, geolocation, mapLayer, markerLayers = [], content, closer, overlay, days = {0:'Su', 1:'Mo', 2:'Tu', 3:'We', 4:'Th', 5:'Fr', 6:'Sa'};

function closeDisclaimer(){
	var expiration_date = new Date();
	expiration_date.setTime(expiration_date.getTime()+(30*24*60*60*1000));
	var cookie_string = "disclaimer="+(Date.now() / 1000 | 0)+";secure; path=/; expires=" + expiration_date.toGMTString();
	document.cookie = cookie_string;
	greybox.close();
}

//insert li at the right alphabetical place in an ul
function addLiAlpha(ul, li){
	var added = false;
	$(ul+" li").each(function(){
		if($(this).text().toLowerCase() > $(li).text().toLowerCase()){
			$(li).insertBefore($(this));
			added = true;
			return false;
		}
	});
	if(!added){
		$(li).appendTo($(ul));
	}
}

var controls = {
	show: function(duration){
		$('#controls').animate({left:'0px'},duration);
		$('#controlsTop').animate({left:'0px',borderBottomRightRadius:0},duration);
		$('.ol-scale-line').animate({left:'328px'},duration);
		if($(window).width() > 400 && $(window).height() > 400){
			$('#w30warning').animate({left:'320px',paddingLeft:'0px'});
		}
	},
	hide: function(){
		$('#controls').animate({left:'-320px'});
		$('#controlsTop').animate({left:'-242px',borderBottomRightRadius:5});
		$('.ol-scale-line').animate({left:'8px'});
		if($(window).width() > 400 && $(window).height() > 400){
			$('#w30warning').animate({left:'0px',paddingLeft:'80px'});
		}
	},
	toggle: function(){
		l = $('#controls').css('left');
		if(l === '0px'){
			controls.hide();
		} else if (l === '-320px') {
			controls.show();
		}
	}
};

var zoom = {
	value: settings.user.zoom,
	change: function(){
		zoom.value = $('#inputZoomLevel').val();
		$('#zoomControl .slider').slider('value', zoom.value);
		zoom.doZoom(zoom.value);
	},
	doZoom: function(value){
		view.animate({zoom: value, duration:500});
	},
	to: function(lat,lng,lvl){
		view.animate({zoom: lvl, center: ol.proj.transform([lng,lat], 'EPSG:4326', 'EPSG:3857'), duration:500});
	},
	reset: function(){
		view.animate({zoom: settings.user.zoom, center: ol.proj.transform(settings.user.center, 'EPSG:4326', 'EPSG:3857'), duration:500});
	}
};

function homeGrid(point){
	var what = $('input[name=setHomeGrid]:checked').val();
	var point = ol.proj.transform(point, 'EPSG:3857', 'EPSG:4326');
	switch(what){
		case 'home':
			home.set(point);
			break;
		case 'grid':
			grid.init(point);
			marker.get(true);
			break;
		case 'both':
			home.set(point);
			grid.init(point);
			marker.get(true);
			break;
		default:
			//do nothing
	}
	if($('#resetSetHomeGrid').is(':checked')){
		$('#setHomeGridNothing').prop('checked', true);
	}
}

var home = {
	layer: null,
	marker: null,
	point: null,
	set: function(point){
		map.removeLayer(home.layer);
		while(point[0] > 180){
			point[0] -= 360;
		}
		while(point[0] < -180){
			point[0] += 360;
		}
		home.point = point;
		settings.user.home = point;
		home.marker = new ol.source.Vector({});
		home.marker.addFeature(new ol.Feature({geometry: new ol.geom.Point(ol.proj.transform(point, 'EPSG:4326', 'EPSG:3857'))}));
		home.layer = new ol.layer.Vector({
			source: home.marker,
			style: new ol.style.Style({
				image: new ol.style.Icon({
					anchor: [16,16],
					anchorXUnits: 'pixels',
					anchorYUnits: 'pixels',
					src: '/img/marker.png'
				})
			})
		});
		map.addLayer(home.layer);
		if(view.getZoom() === 2){
			zoom.to(point[1],point[0],settings.user.zoom);
			settings.user.center = ol.proj.transform(view.getCenter(), 'EPSG:3857', 'EPSG:4326');
		}
	},
	distance: function(location){
		if(home.point == null){
			return '';
		}
		var wgs84Sphere = new ol.Sphere(6378137);
		var dist = wgs84Sphere.haversineDistance(home.point,location)/1000;
		return dist.toFixed(2) + ' km / ' +(dist*0.621371).toFixed(2) + ' miles<br/>';
	}
};

popover = {
	lat: null,
	lng: null,
	coordinates: null,
	feature: null,
	geoName: function(){
		$.getJSON(
			'//data.geohashing.info/geoName/'+this.lat+'/'+this.lng,
			function(json){
				$('#geoName').html(json.geoName);
			}
		);
	},
	show: function(feature){
		this.feature = feature;
		this.coordinates = ol.proj.transform(feature.getGeometry().getCoordinates(), 'EPSG:3857', 'EPSG:4326');
		this.lng = this.coordinates[0];
		this.lat = this.coordinates[1];
		this.geoName();
		if(this.feature.get('global')){
			$('#popup').addClass('global');
			var name = 'Globalhash ' + feature.get('date');
			var grat = 'global';
			var id = this.feature.get('date') + '_global';
		} else {
			$('#popup').removeClass('global');
			var x = this.lng.toString().split('.')[0];
			var y = this.lat.toString().split('.')[0];
			var grat = y + ',' + x;
			var name = 'Geohash ' + this.feature.get('date') + ' ' + y + ' ' + x;
			var id = this.feature.get('date') + '_' + y + '_' + x;
		}
		content.html('<strong>'+name+'</strong> <button onclick="window.open(&quot;http://wiki.xkcd.com/geohashing/'+id+'&quot;)">Meetup</button><br />'+
			this.lat.toFixed(8) + ', ' + this.lng.toFixed(8) + ' <button onclick="zoom.to('+this.lat+','+this.lng+', 15)">Zoom in</button><br />' +
			'<span id="geoName"><span style="color:silver;font-style:italic">[A location name should appear here]</span></span><br/>' +
			home.distance(this.coordinates) + 
			'<button onclick="window.open(&quot;https://data.geohashing.info/hash/' + this.feature.get('date').split('-').join('/') + '/' + grat + '.gpx&quot;)">GPX</button> <button onclick="window.open(&quot;http://geo.crox.net/poster/'+id+'&quot;)">Poster</button> <button onclick="window.open(&quot;http://www.geocaching.com/seek/nearest.aspx?origin_lat='+this.lat+'&amp;origin_long='+this.lng+'&quot;)">Nearby geocaches</button>'
			+ '<button onclick="window.open(&quot;https://maps.google.com/?q='+this.lat+','+this.lng+'&quot;)">Google Maps</button>'
			+ ' <button onclick="window.open(&quot;https://geohashing.info/mobile/' + this.feature.get('date').split('-').join('/') + '/' + grat + '&quot;)">Navigation compass</button> '
			);
		overlay.setPosition(this.feature.getGeometry().getCoordinates());
	},
	hide: function(){
		overlay.setPosition(undefined);
	}
};

loadmap = function(){
	content = $('#popup-content');
	closer = $('#popup-closer');
	overlay = new ol.Overlay({
		element: document.getElementById('popup'),
		autoPan: true,
		autoPanAnimation: {
			duration: 250
		}
	});
	closer.click(function() {
		overlay.setPosition(undefined);
		closer.blur();
		return false;
	});
	
	var defaultZoom = (settings.user.center != '0,0') ? settings.user.zoom : 2;
	view = new ol.View({
		center: ol.proj.transform(settings.user.center, 'EPSG:4326', 'EPSG:3857'),
		minZoom: settings.system.minZoom,
		zoom: defaultZoom,
		maxZoom: settings.system.maxZoom,
		rotation: 0,
		projection: 'EPSG:3857'
	});
	
	geolocation = new ol.Geolocation({
		projection: view.getProjection()
	});
	
	if(settings.user.home == '0,0'){
		geolocation.setTracking(true);
	}
	
	geolocation.on('change', function() {
		homeGrid(geolocation.getPosition());
	});

	//all available basemaps
	var baseMaps = {
		'map': {
			source: new ol.source.OSM(),
			name: 'OpenStreetMap'
		},
		'hyb': {
			source : new ol.source.BingMaps({
				key: settings.system.bingKey,
				imagerySet: 'AerialWithLabels'
			}),
			name: 'Bing maps (Hybrid)'
		},
		'sat': {
			source : new ol.source.BingMaps({
				key: settings.system.bingKey,
				imagerySet: 'Aerial'
			}),
			name: 'Bing maps (Satellite)'
		}
	};
	mapLayer = {
		list: {},
		current: settings.user.type,
		register: function(id, layer, name) {
			this.list[id] = layer;
			addLiAlpha('#mapControl', '<li onclick="javascript:mapLayer.load(\'' + id + '\')"><input type="radio" name="map" value="' + id + '" onchange="javascript:mapLayer.load(\'' + id + '\')" id="map_' + id + '" ' + ((this.current === id) ? 'checked="checked"' : '') + '/> <label for="map_' + id + '">' + name + '</label></li>');
		},
		load: function(id) {
			if (id !== this.current) {
				this.list[id].setVisible(true); //show requested map
				this.list[this.current].setVisible(false); //hide the visible map
				this.current = id;
				$('#map_' + id).prop('checked', true);
			}
		}
	};

	var baseMapLayer = [];
	for (var key in baseMaps) {
		var newLayer = new ol.layer.Tile({
			source: baseMaps[key].source,
			visible: (mapLayer.current === key)
		});
		mapLayer.register(key, newLayer, baseMaps[key].name);
		baseMapLayer.push(newLayer);
	}
	
	var baseMap = new ol.layer.Group({
		layers: baseMapLayer
	});
	
	//limit renderer when using twitter app on iOS (iPad, iPhone, iPod)
	if(navigator.userAgent.match(/Twitter for iP/i)){
		renderer = 'dom';
		$(window).resize(function(){
			$('#map').width($(window).width());
			$('#map').height($(window).height());
			map.updateSize();
		});
	} else {
		renderer = ['canvas', 'dom', 'webgl'];
	}
	map = new ol.Map({
		target: document.getElementById('map'),
		layers: [ baseMap],
		view: view,
		controls: [
			new ol.control.ScaleLine(),
			new ol.control.Attribution({
				collapsible: false
			})
		],
		interactions: ol.interaction.defaults({
			rotate: false,
			altShiftDragRotate: false,
			pinchRotate: false
		}),
		overlays: [overlay],
		renderer: renderer
	});
	
	map.on('moveend', function(){
		$('#inputZoomLevel').val(view.getZoom());
		zoom.change();
	});
	
	map.on('singleclick', function(evt) {
		var feature = map.forEachFeatureAtPixel(evt.pixel, function(feature, layer){
			return feature;
		});
		if(typeof feature !== 'undefined' && feature.get('date')){
			popover.show(feature);
		} else {
			homeGrid(evt.coordinate);
		}
	});
	
	map.on('pointermove', function(evt) {
		var hit = map.forEachFeatureAtPixel(evt.pixel, function(feature, layer){
			return feature.get('date');
		});
		map.getTargetElement().style.cursor = hit ? 'pointer' : '';
	});
};

var greybox = {
	isOpen: false,
	open: function(box){
		$.getJSON('/text/'+box, function(data){
			$('#greybox>div').attr('class', box);
			$('#greybox .title').html(data.title);
			$('#greybox .content').html(data.content);
			$("#greybox .content a[href^='http']").attr('target','_blank');
			$('#greybox').fadeIn();
			$('#greybox div').slideDown();
			$('#greybox .content').scrollTop(0);
			greybox.isOpen = true;
		});
		return false;
	},
	close: function(){
		$('#greybox>div').slideUp();
		$('#greybox').fadeOut();
		$('#greybox .closer').blur();
		greybox.isOpen = false;
	}
};

var gcookie = {
	set: function(){
		// Build the expiration date string:
		var expiration_date = new Date();
		var cookie_string = '';
		expiration_date.setFullYear(expiration_date.getFullYear() + 10);
		// Build the set-cookie string:
		settings.user.center = ol.proj.transform(view.getCenter(), 'EPSG:3857', 'EPSG:4326');
		settings.user.zoom = view.getZoom();
		settings.user.single = !$('#showWeek').is(':checked'); 
		settings.user.dayOf = $('#dayOfWeek').is(':checked') ? 'week' : 'month';
		settings.user.type = $('input[name=map]:checked').val();
		settings.user.controlsVisible = $('#controlsVisible').is(':checked');
		settings.user.resetSetHomeGrid = $('#resetSetHomeGrid').is(':checked');
		cookie_string = "config="+JSON.stringify(settings.user)+";secure; path=/; expires=" + expiration_date.toGMTString();
		// Create/update the cookie:
		document.cookie = cookie_string;
		greybox.open('settings');
	},
	unset: function(){
		cookie_string = "config=;secure ;path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT";
		document.cookie = cookie_string;
		window.alert('Your cookie is deleted');
	}
};

var w30warning = {
	toggle: function(){
		lng = settings.user.grid[0];
		if(lng < -29.0 || lng >= 179){
			var date1 = new Date($('#dateControl .content.datepicker').datepicker('option', 'maxDate'));
			var date2 = new Date($('#datepicker').val());
			var timeDiff = Math.abs(date2.getTime() - date1.getTime());
			var diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));
			if((diffDays === 0 || (diffDays < 7 && $('#showWeek').is(':checked')))){
				this.show();
			} else {
				this.hide();
			}
		} else {
			this.hide();
		}
	},
	show: function(){
		$('#w30warning .date').html($('#dateControl .content.datepicker').datepicker('option', 'maxDate'));
		$('#w30warning').fadeIn();
	},
	hide: function(){
		$('#w30warning').fadeOut();
	},
	reset: function(){
		if($(window).width() > 400 && $(window).height() > 400){
			l = $('#controls').css('left');
			if(l === '0px'){
				$('#w30warning').css('left', '320px');
				$('#w30warning').css('padding-left', '0px');
			} else if (l === '-320px') {
				$('#w30warning').css('left', '0px');
				$('#w30warning').css('padding-left', '80px');
			}
		} else {
			$('#w30warning').css('left', '');
			$('#w30warning').css('padding-left', '');
		}
	}
};