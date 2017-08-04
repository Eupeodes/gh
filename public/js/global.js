/*
 * file:		script.js
 * author:		Marten Tacoma
 * contents:	all custom js
 */

/*
 * TODO:
 * create popups like http://openlayers.org/en/v3.12.1/examples/popup.html
 */

$(window).load(function() {
	loadmap();
	
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