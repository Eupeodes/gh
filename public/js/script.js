/*
 * file:		script.js
 * author:		Marten Tacoma
 * contents:	all custom js
 */

/*
 * TODO:
 * create popups like http://openlayers.org/en/v3.12.1/examples/popup.html
 */
var map, view, settings, ol, xs,xt,xl, ys,yt,yl, gridLayer, gridVector, geolocation, mapLayer, markerLayers = [], content, closer, overlay, days = {0:'Su', 1:'Mo', 2:'Tu', 3:'We', 4:'Th', 5:'Fr', 6:'Sa'};


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
	},
	hide: function(){
		$('#controls').animate({left:'-320px'});
		$('#controlsTop').animate({left:'-280px',borderBottomRightRadius:5});
		$('.ol-scale-line').animate({left:'8px'});
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

var date = {
	value: null,
	change: function(){
		date.value = $('#datepicker').val();
		$('#dateControl .content.datepicker').datepicker('setDate', date.value);
		marker.get();
	},
	refreshMaxDate: function(){
		$.getJSON(
				'/maxDate',
				function(json){
					if($('#dateControl .content.datepicker').datepicker('option', 'maxDate') !== json.maxDate){
						$('#dateControl .content.datepicker').datepicker('option', 'maxDate', json.maxDate);
						marker.get(true, true);
						date.setTimer(10);
					} else {
						date.setTimer(json.nextCheck);
					}
				}
			);
	},
	setTimer: function(time){
		setTimeout(
				function(){
				date.refreshMaxDate();
			},
			time*1000
		);
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
		var animation = ol.animation.zoom({
			duration: 200,
			resolution: view.getResolution()
		});
		map.beforeRender(animation);
		view.setZoom(value);
	},
	to: function(lat,lng,lvl){
		var animation = ol.animation.zoom({
			duration: 200,
			resolution: view.getResolution()
		});
		map.beforeRender(animation);
		view.setZoom(lvl);
		zoom.setCenter(ol.proj.transform([lng,lat], 'EPSG:4326', 'EPSG:3857'));
	},
	setCenter: function(point){
		var size = map.getSize();
		var sideBar = parseInt($('#controls').css('width'),10)+parseInt($('#controls').css('left'),10);
		view.centerOn(point,size,[((size[0]-sideBar)/2)+sideBar,size[1]/2]);
	},
	reset: function(){
		zoom.doZoom(settings.user.zoom);
		zoom.setCenter(ol.proj.transform(home.point, 'EPSG:4326', 'EPSG:3857'));
	}
};

var home = {
	layer: null,
	marker: null,
	point: null,
	set: function(point){
		map.removeLayer(home.layer);
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
		}
	},
	distance: function(location){
		var wgs84Sphere = new ol.Sphere(6378137);
		var dist = wgs84Sphere.haversineDistance(home.point,location)/1000;
		return dist.toFixed(2) + ' km / ' +(dist*0.621371).toFixed(2) + ' miles';
	}
};

var marker = {
	curDate: null,
	date: null,
	init: function(){
		var c = home.point;
		var baseLat = Math.floor(Math.abs(c[1]));
		var baseLng = Math.floor(Math.abs(c[0]));
		var latSign = (c[1]>=0 ? 1 : -1);
		var lngSign = (c[0]>=0 ? 1 : -1);
		
		if(baseLat === 0){
			ys = [0,0,1];
			if(latSign === 1){
				yt = [-1,1,1];
				yl = [[-1,2],[0,1]];
			} else {
				yt = [1,-1,-1];
				yl = [[-2,1],[-1,0]];
			}
		} else if (baseLat === 89){
			ys = [88,89];
			yt = [latSign, latSign];
			yl = [[latSign*88,latSign*89.99],[latSign*89]];
		} else {
			ys = [];
			yt = [];
			for(var y=baseLat-1;y<=baseLat+1;y++){
				ys.push(y);
				yt.push(latSign);
			}
			if(baseLat === 88){
				yl = [[latSign*87,latSign*89.99],[latSign*88,latSign*89]];
			} else {
				yl = [[latSign*(baseLat-1),latSign*(baseLat+2)],[latSign*baseLat,latSign*(baseLat+1)]];
			}
		}
		if(baseLng === 0){
			xs = [0,0,1];
			if(lngSign === 1){
				xt = [-1,1,1];
				xl = [[-1,2],[0,1]];
			} else {
				xt = [1,-1,-1];
				xl = [[-2,1],[-1,0]];
			}
		} else if (baseLng === 179){
			xs = [178,179,179];
			xt = [lngSign, 1, -1];
			xl = [[lngSign*178, lngSign*181], [lngSign*179, lngSign*180]];
		} else {
			xs = [];
			xt = [];
			for(var x=baseLng-1;x<=baseLng+1;x++){
				xs.push(x);
				xt.push(lngSign);
			}
			xl = [[lngSign*(baseLng-1), lngSign*(baseLng+2)], [lngSign*baseLng, lngSign*(baseLng+1)]];
		}
		marker.drawGrid();
	},
	get: function(force, keepPopover){
		force = typeof force !== 'undefined' ? force : false;
		keepPopover = typeof keepPopover !== 'undefined' ? keepPopover : false;
		marker.date = $('#datepicker').val();
		if(marker.date !== marker.curDate || force){
			for(var i=0;i<markerLayers.length;i++){
				map.removeLayer(markerLayers[i]);
			}
			if(!keepPopover){
				popover.hide();
			}
			markerLayers = [];
			marker.curDate = marker.date;
			var url = '/hash/';
			if($('#showWeek').is(':checked')){
				url += 'wk/';
			}
			url += marker.date.split('-').join('/')+'.json';
			$.getJSON(
				url,
				function(json){
					marker.draw(json);
				}
			);
		}
	},
	draw: function(json){
		if(Object.prototype.toString.call(json) === '[object Array]'){
			for(var i=0; i<json.length; i++){
				marker.doDraw(json[i]);
			}
		} else {
			marker.doDraw(json);
		}
	},
	doDraw: function(json){
		var date = new Date(json.date);
		date.setTime( date.getTime() + date.getTimezoneOffset()*60*1000 );
		var day = $('#dayOfWeek').is(':checked') ? days[date.getDay()] : date.getDate();
		var bgColor = $('#markerControl .colorPicker.selected').attr('bgcolor');
		var fgColor = $('#markerControl .colorPicker.selected').attr('fgcolor');
		s = new ol.source.Vector({});
		for(var x=0;x<xs.length;x++){
			if((xs[x] >= 30 & xt[x] === -1 & json.west !== null) || xs[x] < 30 || xt[x] === 1){
				for(var y=0;y<ys.length;y++){
					var point = null;
					if(xs[x] >=30 & xt[x] === -1){
						point = new ol.geom.Point(ol.proj.transform([parseFloat(xt[x]*(xs[x]+json.west.lng)), parseFloat(yt[y]*(ys[y]+json.west.lat))], 'EPSG:4326', 'EPSG:3857'));
					} else {
						point = new ol.geom.Point(ol.proj.transform([parseFloat(xt[x]*(xs[x]+json.east.lng)), parseFloat(yt[y]*(ys[y]+json.east.lat))], 'EPSG:4326', 'EPSG:3857'));
					}
					s.addFeature(new ol.Feature({
						geometry: point,
						date:json.date,
						global:false
					}));
				}
			}
		}
		markerLayers.push(new ol.layer.Vector({
			source: s,
			style: new ol.style.Style({
				image: new ol.style.Icon({
					anchor: [13,36],
					anchorXUnits: 'pixels',
					anchorYUnits: 'pixels',
					src: '/img/marker.php?bg='+bgColor+'&fg='+fgColor+'&text='+day
				})
			})
		}));
		map.addLayer(markerLayers[markerLayers.length-1]);
		
		point = new ol.geom.Point(ol.proj.transform([parseFloat(json.global.lng), parseFloat(json.global.lat)], 'EPSG:4326', 'EPSG:3857'));
		s = new ol.source.Vector({});
		s.addFeature(new ol.Feature({
			geometry: point,
			date: json.date,
			global: true
		}));
		markerLayers.push(new ol.layer.Vector({
			source: s,
			style: new ol.style.Style({
				image: new ol.style.Icon({
					anchor: [25,72],
					anchorXUnits: 'pixels',
					anchorYUnits: 'pixels',
					src: '/img/marker.php?bg='+bgColor+'&fg='+fgColor+'&global&text='+day
				})
			})
		}));
		map.addLayer(markerLayers[markerLayers.length-1]);
	},
	drawGrid: function(){
		map.removeLayer(gridLayer);
		gridVector = new ol.source.Vector({});
		for(var x=0;x<xl[1].length;x++){
			marker.drawLine([[xl[1][x],yl[0][0]],[xl[1][x],yl[0][1]]]);
		}
		for(var y=0;y<yl[1].length;y++){
			marker.drawLine([[xl[0][0],yl[1][y]],[xl[0][1],yl[1][y]]]);
		}
		gridLayer = new ol.layer.Vector({
			source: gridVector,
			style: new ol.style.Style({
				fill: new ol.style.Fill({ color: '#0000FF', weight: 4 }),
				stroke: new ol.style.Stroke({ color: '#0000FF', width: 2 })
			})
		});
		map.addLayer(gridLayer);
	},
	drawLine: function(points){
		for (var i = 0; i < points.length; i++) {
			points[i] = ol.proj.transform(points[i], 'EPSG:4326', 'EPSG:3857');
		}
		gridVector.addFeature(new ol.Feature({geometry: new ol.geom.LineString(points)}));
	}
};

popover = {
	lat: null,
	lng: null,
	coordinates: null,
	feature: null,
	geoName: function(){
		$.getJSON(
			'/geoName/?lat='+this.lat+'&lng='+this.lng,
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
			home.distance(this.coordinates) + '<br/>' +
			'<button onclick="window.open(&quot;/hash/' + this.feature.get('date').split('-').join('/') + '/' + grat + '.gpx&quot;)">GPX</button> <button onclick="window.open(&quot;http://geo.crox.net/poster/'+id+'&quot;)">Poster</button> <button onclick="window.open(&quot;http://www.geocaching.com/seek/nearest.aspx?origin_lat='+this.lat+'&amp;origin_long='+this.lng+'&quot;)">Nearby geocaches</button>'
			+ '<button onclick="window.open(&quot;https://maps.google.com/?q='+this.lat+','+this.lng+'&quot;)">Google Maps</button>'
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
	
	if(settings.user.center == '0,0'){
		geolocation.setTracking(true);
	}
	
	geolocation.on('change', function(evt) {
		zoom.setCenter(geolocation.getPosition());
		home.set(ol.proj.transform(geolocation.getPosition(), 'EPSG:3857', 'EPSG:4326'));
		geolocation.setTracking(false);
		marker.init();
		marker.get(true);
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
			addLiAlpha('#mapControl', '<li onclick="javascript:mapLayer.load(\'' + id + '\')"><input type="radio" name="map" onchange="javascript:mapLayer.load(\'' + id + '\')" id="map_' + id + '" ' + ((this.current === id) ? 'checked="checked"' : '') + '/> <label for="map_' + id + '">' + name + '</label></li>');
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
			home.set(ol.proj.transform(evt.coordinate, 'EPSG:3857', 'EPSG:4326'));
			marker.init();
			marker.get(true);
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
			$('#greybox .title').html(data.title);
			$('#greybox .content').html(data.content);
			$('#greybox').fadeIn();
			$('#greybox div').slideDown();
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

$(window).load(function() {
	loadmap();
	$('#showWeek').change(function(){
		marker.get(true);
	});
	$('#datepicker').change(function(){
		date.change();
	});
	$('#dateControl .content.datepicker').datepicker({
		changeMonth: true,
		changeYear: true,
		dateFormat: 'yy-mm-dd',
		showOtherMonths: true,
		selectOtherMonths: true,
		firstDay: 1,
		minDate: settings.system.minDate,
		maxDate: settings.system.maxDate,
		altField: '#datepicker',
		defaultDate: settings.system.date,
		nextText: '&gt;',
		prevText: '&lt;',
		hideIfNoPrevNext: true,
		onSelect: function(x){
			marker.get();
		}
	});
	date.setTimer(settings.refreshMaxDate);
	
	$('#inputZoomLevel').change(function(){
		zoom.change();
	});
	$('#inputZoomLevel').val(settings.user.zoom);
	$('#zoomControl .slider').slider({
		value: settings.user.zoom,
		min: settings.system.minZoom,
		max: settings.system.maxZoom,
		slide: function(event, ui){
			$('#inputZoomLevel').val(ui.value);
			zoom.doZoom(ui.value);
		}
	});
	if($(window).width()>640 & settings.user.controlsVisible){
		controls.show(0);
	}
	if(settings.user.center != '0,0'){
		home.set(settings.user.center);
		marker.init();
		marker.get();
	}
	$("input[name='dayOf']").change(function(){
		marker.get(true, true);
	});
	$("#markerControl .colorPicker").click(function(evt){
		if(!$(evt.target).hasClass('selected')){
			$("#markerControl .colorPicker").removeClass('selected');
			$('#'+evt.target.id).addClass('selected');
			settings.user.colorSet = $('#'+evt.target.id).attr('setid');
			marker.get(true, true);
		}
	});
	$('#openChangelog').click(function(){
		greybox.open('changelog');
		return false;
	});
	$('#greybox').click(function(){greybox.close()});
	$('#greybox>div').click(function(evt) {
		evt.stopPropagation()
	});
	$('#greybox .closer').click(function(){
		greybox.close();
		return false;
	});
	    $(document).keyup(function(g) {
        c = g.keyCode || g.which;
        if (c === 27) {
            if (greybox.isOpen) {
				greybox.close()
            } else {
                popover.hide()
            }
        }
    })
});
