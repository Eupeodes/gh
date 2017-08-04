/*
 * file:		script.js
 * author:		Marten Tacoma
 * contents:	all custom js
 */

/*
 * TODO:
 * create popups like http://openlayers.org/en/v3.12.1/examples/popup.html
 */

 var grid = {
	init: function(point){
		settings.user.grid = point;
		var c = point;
		var baseLat = Math.floor(Math.abs(c[1]));
		var baseLng = Math.floor(Math.abs(c[0]));
		var latSign = (c[1]>=0 ? 1 : -1);
		var lngSign = (c[0]>=0 ? 1 : -1);
		while(baseLng > 180){
			baseLng -= 360;
			settings.user.grid[0] -= lngSign*360;
		}
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
		this.draw();
	},
	draw: function(){
		map.removeLayer(gridLayer);
		gridVector = new ol.source.Vector({});
		for(var x=0;x<xl[1].length;x++){
			this.drawLine([[xl[1][x],yl[0][0]],[xl[1][x],yl[0][1]]]);
		}
		for(var y=0;y<yl[1].length;y++){
			this.drawLine([[xl[0][0],yl[1][y]],[xl[0][1],yl[1][y]]]);
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

var marker = {
	curDate: null,
	date: null,
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
			var url = '//data.geohashing.info/hash/';
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
			w30warning.toggle();
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
				'//data.geohashing.info/maxDate',
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
	
	if($(window).width()>640 && settings.user.controlsVisible){
		controls.show(0);
	}
	
	if(settings.user.home != '0,0'){
		home.set(settings.user.home);
	}
	if(settings.user.grid != '0,0'){
		grid.init(settings.user.grid);
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
	
	$('#toggleControls').click(function(){
		controls.toggle();
	});
	
	$('#openChangelog').click(function(){
		greybox.open('changelog');
		return false;
	});
	
	$('#openDisclaimer').click(function(){
		greybox.open('disclaimer');
		return false;
	})
	$('#openHelp').click(function(){
		greybox.open('help');
		return false;
	});
	
	$('#greybox').click(function(){
		greybox.close();
	});
	
	$('#greybox>div').click(function(evt) {
		evt.stopPropagation();
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
    });
	
	$("a[href^='http']").attr('target','_blank');
	
	$('input[name=setHomeGrid]').change(function(){
		geolocation.setTracking(false);
	});
	
	$('#redetectHome').click(function(){
		if($('input[name=setHomeGrid]:checked').val() === 'nothing'){
			alert('Please first select what to detect');
		} else {
			geolocation.setTracking(true);
		}
	});
	$('#controls').niceScroll();
	
	if(settings.system.showDisclaimer){
		greybox.open('disclaimer');
	}
	
	$('#w30warning .close').click(function(){w30warning.hide();});
	$(window).on('resize', function(){
		w30warning.reset();
	});
});