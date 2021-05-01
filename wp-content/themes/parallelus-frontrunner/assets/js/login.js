jQuery(document).ready(function($){

	// make sure the background covers on all devices
	$(window).resize( function(){
		// clear min-height
		$('html').css('min-height', '0');
		// get new value
		minHeight = $('#login').outerHeight();
		$('html').css('min-height', minHeight+'px');
	});

	// support mobile device rotation
	window.addEventListener("orientationchange", function() {
		$(window).trigger('resize');
	}, false);

	// trigger it to start
	$(window).trigger('resize');

});