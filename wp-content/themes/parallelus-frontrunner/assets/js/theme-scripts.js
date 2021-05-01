jQuery(document).ready(function($){

	// Show/hide video and scroll to
	// ------------------------------------------------------------------------
	$(".video-list").on("click", ".video-thumbnail", function(){

		videoIndex = $(this).data('video-index');
		element = $(".video-element-"+videoIndex);
		wrapper = element.closest('.video-wrapper');

		// Mark the active container
		$('.video-element').removeClass('open');
		element.addClass('open');

		// Show playerf
		wrapper.addClass('show');
		wrapper.css({ overflow: 'hidden'});

		// Top offset
		var offset = 20;
		if($("body").hasClass("videos") || $("body").hasClass("post-type-archive-political-video")){
			offset = 80;
		}
		// Scroll to view
		setTimeout((function() {
			elementTo = $('.video-list').first();
			$('html, body').animate({
				scrollTop: elementTo.offset().top - offset
			});
		}), 150);
	});

	// Close video button
	$(".video-wrapper .close-button").on("click", function(){
		$(this).closest('.video-wrapper').removeClass('show');
	});

	// Ajax Load More Videos
	MoreVideos = false;

	$("#more_videos").on("click", function(e) {

		e.preventDefault();

		var $moreBtn = $(this),
			videoPaged = $moreBtn.data('paged') + 1, // current page
			maxPaged = $moreBtn.data('max'); // max pages

		$moreBtn.blur();

		if ( videoPaged <= maxPaged && !MoreVideos ) {

			// Disable the button (no double clicks!)
			$moreBtn.attr('disabled', 'disabled');

			// Query
			videoRequest = $.ajax({
				url: ThemeJS.ajax_url,
				method: "POST",
				data: {
					paged : videoPaged,
					political_video_action : 'political_videos_more_posts'
				},
				dataType: "text"
			});

			// Success
			videoRequest.done(function( videos ) {
				MoreVideos = jQuery.parseJSON(videos);
				// Display the videos
				showMoreVideos(MoreVideos, videoPaged, maxPaged, $moreBtn);
				// Re-enable more button
				$moreBtn.prop("disabled", false);

			});

			// Error
			videoRequest.fail(function( jqXHR, textStatus ) {
				console.log( "Request failed: " + textStatus );
			});
		} else if (MoreVideos) {
			// Display the videos
			showMoreVideos(MoreVideos, videoPaged, maxPaged, $moreBtn);
		}
	});

	function is_browser_safari() {
		var is_chrome = navigator.userAgent.indexOf('Chrome') > -1;
		var is_safari = navigator.userAgent.indexOf("Safari") > -1;
		if( ( is_chrome ) && ( is_safari ) ) {
			is_safari = false;
		}

		return is_safari;
	}
	if( $('#header.header-bg div.header-bg-wrapper header.page-header').length > 0 ) {
		if( is_browser_safari() ) {
			$('div.header-bg-wrapper').css({'height':$('#header.header-bg').css('height')});
		}
		$('div.header-bg-wrapper').show();
	}
	if( $('div.header-bg-wrapper div.header-inner.logo-container.menu-logo-middle').length > 0 ) {
		if( is_browser_safari() ) {
			$('div.header-bg-wrapper').css({'height':$('#header.header-large').css('height')});
		}
		$('div.header-bg-wrapper').show();
	}

	// Show More Videos
	function showMoreVideos( videos, page, max, btn ) {

		videos = (typeof videos == 'object') ? videos : false;
		page   = (typeof page !== 'undefined') ? page : 0;
		max    = (typeof max !== 'undefined') ? max : 1;
		btn    = (typeof btn !== 'undefined') ? btn : false;

		if (videos && btn) {

			next = videos[page];
			end = false;

			if (typeof next === 'object') {
				// add the videos
				$.each(next, function(key, value) {
					// Thumbnail
					thumb = $('<div class="col-md-4 col-sm-6"><article class="video-entry"><div class="video-loading"></div><div class="thumbnail-wrapper video-thumbnail video-paged-'+page+'" id="thumb-'+value.video_id+'" data-video-index="'+value.video_id+'" style="background-image: url('+value.video_thumb+'); opacity: 0;"><i class="fa fa-play-circle"></i><div class="overlay"></div></div><h3 class="video-title">'+value.video_title+'</h3><p class="video-desc">'+value.video_desc+'</p></article></div>');
					$( "#player_list" ).append( thumb );

					// Player
					player = $('<div class="video-element video-element-'+value.video_id+'"><div id="'+value.video_id+'" class="video-youtube"></div></div>');
					$( "#player_container" ).append( player );

					loadYTPlayer();
				});

				if ( page >= max ) {
					// Hide the more button
					btn.animate({
						'opacity': 0
					}, 800, function() {
						btn.slideUp(200);
					});
				} else {
					// Update paged variable
					btn.data('paged', page);
				}
			}
		}
	}


	// Events Timeline
	// ------------------------------------------------------------------------
	MoreEvents = false;

	// Ajax Load More Videos
	$("#more_events").on("click", function(e) {

		e.preventDefault();

		var $moreBtn = $(this),
			eventPaged = $moreBtn.data('paged') + 1, // current page
			maxPaged = $moreBtn.data('max'); // max pages

		$moreBtn.blur();

		if ( eventPaged <= maxPaged && !MoreEvents ) {

			// Disable the button (no double clicks!)
			$moreBtn.attr('disabled', 'disabled');

			// Query
			eventRequest = $.ajax({
				url: ThemeJS.ajax_url,
				method: "POST",
				data: {
					paged : eventPaged,
					political_event_action : 'political_events_more_posts'
				},
				dataType: "text"
			});

			// Success
			eventRequest.done(function( events ) {
				MoreEvents = jQuery.parseJSON(events);
				// Display the events
				showMoreEvents(MoreEvents, eventPaged, maxPaged, $moreBtn);
				// Re-enable more button
				$moreBtn.prop("disabled", false);
			});

			// Error
			eventRequest.fail(function( jqXHR, textStatus ) {
				console.log( "Request failed: " + textStatus );
			});
		} else if (MoreEvents) {
			// Display the events
			showMoreEvents(MoreEvents, eventPaged, maxPaged, $moreBtn);
		}
	});

	// Show More Events
	function showMoreEvents( events, page, max, btn ) {

		events = (typeof events == 'object') ? events : false;
		page   = (typeof page !== 'undefined') ? page : 0;
		max    = (typeof max !== 'undefined') ? max : 1;
		btn    = (typeof btn !== 'undefined') ? btn : false;

		if (events && btn) {

			next = events[page];
			end = false;

			if (typeof next === 'object') {
				// add the events
				$.each(next, function(key, value) {

					eventCount = btn.data('count') + 1; // event count
					btn.data('count', eventCount);

					// alternating class
					alt_class = 'inverted';
					if (eventCount%2 == 0) {
						alt_class = 'standard';
					}

					// Event
					entry = $('<li class="timeline-date event-paged-'+page+'"><div class="date">'+value.event_day+'</div><div class="month">'+value.event_month+'</div></li><li class="timeline-'+alt_class+'"><div class="circle"></div><div class="tl-panel">'+value.event_title+'<div class="tl-body"><p>'+value.event_desc+'</p><div class="time"><i class="fa fa-clock-o"></i> '+ value.event_time +'</div>'+value.event_place+'</div></div>'+value.event_date_title+'</li>');
					$( "#events_list" ).append( entry );
				});

				if ( page >= max ) {
					end = true;
					// Hide the more button
					btn.animate({
						'opacity': 0
					}, 800, function() {
						btn.slideUp(200);
					});
				} else {
					// Update paged variable
					btn.data('paged', page);
				}
			} else {
				end = true;
			}

			if (end) {
				// Fade out the time "line"
				$( "#events_list" ).find('.end-of-line').fadeIn(800);
			}
		}
	}

	// Home page default-menu
	// ------------------------------------------------------------------------
	var win_Width = $(window).width();

	$(window).on("scroll", function(){
		// Ignore on small screens
		if (win_Width >= 1200) {
			// If the wrapper has 'do-transition' class...
			if ($('.navbar-wrapper').hasClass('do-transition')) {
				$nav = $("#nav-main");
				if($(this).scrollTop() >= 905) {
					// Show horizontal nav
					if ($nav.hasClass('navbar-vertical')) {
						$nav.stop( true, true )
							.css('opacity', 0)
							.removeClass('navbar-vertical')
							.addClass('navbar-fixed-top')
							.animate({ 'opacity': 1 }, 600 );
					}
				} else {
					// Show vertical nav
					if ($nav.hasClass('navbar-fixed-top') && !$nav.hasClass('fading')) {
						$nav.stop( true, true )
							.addClass('fading')
							.animate({ 'opacity': 0 }, 200, function(){
								$nav.removeClass('navbar-fixed-top')
								.addClass('navbar-vertical')
								.animate({ 'opacity': 1 }, 200, function(){
									$nav.removeClass('fading');
								});
							});
					}
				}
			}
		}
	});
	// Trigger manually once after loading
	$(window).trigger('scroll');

	// Update width var on resize
	$(window).resize(function() {
		win_Width = $(window).width();
	if( $('div.header-bg-wrapper div.header-inner.logo-container.menu-logo-middle').length > 0 )
		$('div.header-bg-wrapper').css({'height':$('#header.header-large').css('height')});

	});

	// Page page sticky navbar
	// ------------------------------------------------------------------------
	if ( $('.navbar-sticky').length ) {

		stickyElement = $('.navbar-sticky');
		navbarTop = stickyElement.offset().top;

		$(window).scroll(function () {
			var y = $(this).scrollTop();
			if (y >= navbarTop && !stickyElement.hasClass('navbar-fixed-top')){
				stickyElement.addClass('navbar-fixed-top');
			}
			else if(y < navbarTop && stickyElement.hasClass('navbar-fixed-top')){
				stickyElement.removeClass('navbar-fixed-top');
			}

		});

		// adjust for orientation change
		$(window).resize(function () {
			stickyElement.removeClass('navbar-fixed-top');
			navbarTop = stickyElement.offset().top;
			$(window).trigger('scroll');
		});
	}


	// Home page sub menu
	// ------------------------------------------------------------------------

	$(".sub-menu").on("mouseover", function(){
		$(this).parent().addClass("open");
	});
	$(".sub-menu").on("mouseout", function(){
		$(this).parent().removeClass("open");
	});


	// Donate page, donate widget click event
	// ------------------------------------------------------------------------
	$(".box label").on("click", function(){
	   if($(this).hasClass("on")){
		   return;
	   }
	   $(".box label").removeClass("on");
		$(this).addClass("on");
	});


	// owl carousel
	// ------------------------------------------------------------------------
	if ( $('.featured-carousel').length ) {
		$(".featured-carousel").owlCarousel({
			items: 1,
			loop: true,
			autoplay: true,
			autoplayHoverPause: true,
			autoplayTimeout: 3800,
			autoplaySpeed: 800,
			navSpeed: 500,
			dots: false,
			nav: true,
			navText: [
				'<i class="fa fa-angle-left"></i>',
				'<i class="fa fa-angle-right"></i>'
			]
		});
	}


	// Responsive videos
	// ------------------------------------------------------------------------
	if (typeof $().fitVids == 'function') {
		$(".container").fitVids({ ignore: '.video-wrapper, .video-element'});
	}


	// Tooltips
	// ------------------------------------------------------------------------
	$('[data-toggle="tooltip"]').tooltip({
		placement: function(tip, trigger) {
			return 'auto top';
		}
	});


	// Popovers
	// ------------------------------------------------------------------------
	$('[data-toggle="popover"]').popover();

	// Next/Prev Post Nav
	$('.nav-previous > a, .nav-next > a').popover({
		html : true,
		placement : 'top',
		trigger : 'hover',
		delay : { "show": 500, "hide": 100 },
		title : function() {
			return $(this).find('.meta-nav-title').html();
		},
		content : function() {
			if ( $(this).parent().hasClass('w-image') ) {
				var img = $('<img class="placeholder" width="244" height="122" style="visibility:hidden" >');
				img.attr('src', 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAPQAAAB6CAMAAAC/S45kAAAAA1BMVEX///+nxBvIAAAAAXRSTlMAQObYZgAAADNJREFUeNrtwQENAAAAwqD3T20PBxQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA8Gt0wgABrQSTgQAAAABJRU5ErkJggg==');
				var postImg = $(this).find('.meta-nav-img').text();
				var container = $('<div class="popover-img" style="background-image: url('+postImg+'); height: 122px;"></div>').append(img);
			} else {
				container = '';
			}
			return container;
		},
		template : '<div class="popover post-nav-popover" role="tooltip"><div class="arrow"></div><div class="popover-content"></div><h3 class="popover-title"></h3></div>'
	});


	// Style helpers
	// ------------------------------------------------------------------------
	if ($('#footer.with-overlap').length) {
		$('#footer.with-overlap').prev().addClass('before-footer-overlap');
	}


	// Navbar Hover/Click Responsive Behavior
	// ------------------------------------------------------------------------
	collapseSize = 768;

	// hover sub-menu items
	$('.navbar-nav a').click( function(e) {
		$this = $(e.target);
		href = $this.attr('href'); // Link URL

		// Check link value
		if (href === undefined || !href.length || href === '#' || href === 'javascript:;') {
			href = false;
		}
		// Link behavior
		if ($this.hasClass('dropdown-toggle')) {
			// Parent menu items
			if ($(window).width() > collapseSize) {
				if (href) {
					// large screens, follow the parent menu link when clicked
					if (e.which !== 2 && e.target.target != '_blank') {
						window.location.href = href;
					}
				}
			 } else if ( $this.parent().hasClass('open') && href !== false) {
				// small screens, 1st tap opens sub-menu & 2nd tap follows link
				$(document).trigger('collapse-menus');
				window.location.href = href;
			}
		} else {
			// All other menu items, close menu on click
			$(document).trigger('collapse-menus');
		}
	});
	// Keep parent menus open on sub-menu expand
	$(document).on('show.bs.dropdown', function(obj) {
		if ($(window).width() <= collapseSize) {
			$(obj.target).parents('.show-on-hover').addClass('open');
		}
	});
	$('.navbar a:not(.dropdown-toggle)').click( function(e) {

		$this = $(e.target);
		href = $this.attr('href'); // Link URL

		// Check link value
		if (href === undefined || !href.length || href === '#' || href === 'javascript:;') {
			href = false;
		}
		// Link behavior
		if ($(window).width() > collapseSize) {
			if (href) {
				// large screens, follow the parent menu link when clicked
				if (e.which !== 2 && e.target.target != '_blank') {
					window.location.href = href;
				}
			}
		 } else if ( $this.parent().hasClass('open') && href !== false) {
			// small screens, 1st tap opens sub-menu & 2nd tap follows link
			$(document).trigger('collapse-menus');
			window.location.href = href;
		}
	});
	// Close all menus
	$(document).on('collapse-menus', function () {
		$('.collapse.in').removeClass('in').children().removeClass('open');
	});
	// Hover styling helpers
	$('.navbar-nav > li.show-on-hover').hover(function() {
		if ($(window).width() > collapseSize) {
			$(this).addClass('open');
		}
	}, function() {
		if ($(window).width() > collapseSize) {
			$(this).removeClass('open');
		}
	});


}); // END - jQuery(document).ready()


// YouTube in Video Sections
// ------------------------------------------------------------------------
var players = {};

// Initial trigger for loading videos (called by required script: https://www.youtube.com/iframe_api)
function onYouTubeIframeAPIReady() {
	loadYTPlayer(); // load the videos
}

// Load videos
function loadYTPlayer() {
	jQuery(".video-youtube").each( function() {

		// videoID = jQuery(this).children('div').attr('id') || 0;
		$this = jQuery(this);
		videoID =  $this.attr('id') || 0;
		$this.removeClass('video-youtube');

		if (videoID && typeof(videoID) !== 'undefined') {

			players[videoID] = new YT.Player(videoID, {
			  videoId: videoID,
			  playerVars: {
				showinfo: 0,
				rel: 0,
				wmode: 'opaque',
			  },
			  events: {
				'onReady': onYTPlayerReady,
			  }
			});
		}
	});

	// Close button
	jQuery(".video-wrapper .close-button").on("click", function(){
		stopYTVideo(players, false); // Stop all playing videos
	});
}

// Triggered when video player is ready. Used to set events.
function onYTPlayerReady(event) {

	// Fallback support for getVideoData removed by YouTube API - 2017/11/15
	if (!event.target.getVideoData) {
		for(var j in event.target) {
			if (event.target[j].videoData) {
				(function(j) {
					event.target.getVideoData = function() {
						return event.target[j].videoData;
					}
				})(j);
			}
		}
	}

	// Current video
	videoID = event.target.getVideoData().video_id;
	$video = jQuery('#thumb-'+videoID);

	// Thumbnail click behaviors
	$video.on('click', function() {
		// Stop all playing videos
		stopYTVideo(players, false);

		// Play the video
		var player = event.target;
		if (player && typeof(player.playVideo) !== 'undefined') {
			setTimeout( function() { player.playVideo(); }, 900 );// 0.9 second delay
		}
	});

	// If hidden, show the thumbnail
	if ($video.css('opacity') == 0) {
		$video.animate({'opacity':1},200);
	}
}

 // Stop playing action
function stopYTVideo(players, video) {
	// Loop through and stop all videos
	for (var key in players) {
	   if (players.hasOwnProperty(key)) {
		   var player = players[key] || 0;
			if (player && typeof(player.stopVideo) !== 'undefined') {
				player.stopVideo();
			}
		}
	}
}