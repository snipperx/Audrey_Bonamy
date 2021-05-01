jQuery(function($) {
	$('.political-options-datepicker').datepicker({
	    showButtonPanel: 	false,
	    closeText: 			dp_translations.closeText,
	    currentText: 		dp_translations.currentText,
	    monthNames: 		dp_translations.monthNames,
	    monthNamesShort: 	dp_translations.monthNamesShort,
	    dayNames: 			dp_translations.dayNames,
	    dayNamesShort: 		dp_translations.dayNamesShort,
	    dayNamesMin: 		dp_translations.dayNamesMin,
	    dateFormat: 		dp_translations.dateFormat,
	    firstDay: 			dp_translations.firstDay,
	    isRTL: 				dp_translations.isRTL,
	});
	$('.political-options-datepicker').each(function() {
		$(this).datepicker({
			onSelect: function(date, el) {
				$(this).attr('value', date);
			}
		});
	});
	if($('.political-options-datepicker').length) {
		dates_validation();	
	}
	$('.political-options-datepicker').change(function(e) {
		var date = new Date($(this).datepicker( 'getDate'));
		$('input[name="'+$(this).attr('name')+'_default'+'"]').val(date.getMonth() + 1 + '/' + date.getDate() + '/' + date.getFullYear());
		dates_validation();
	});
	$('select[name="event_time_start"]').change(function(e) {
		dates_validation();		
	});


	var frame,
		images = $('#cfpf-format-gallery-ids-field').val(),   //'<?php echo get_post_meta( $post->ID, 'tz_image_ids', true ); ?>',
		selection = loadImages(images);

	$('#cfpf-format-gallery-preview .none a').on('click', function(e) {
		e.preventDefault();

		// Set options for 1st frame render
		var options = {
			title: 'Gallery Post Format',
			state: 'gallery-edit',
			frame: 'post',
			selection: selection
		};

		// Check if frame or gallery already exist
		if( frame || selection ) {
			options['title'] = 'Gallery Post Format';
		}

		frame = wp.media(options).open();

		// Tweak views
		frame.menu.get('view').unset('cancel');
		frame.menu.get('view').unset('separateCancel');
		frame.menu.get('view').get('gallery-edit').el.innerHTML = 'Edit Gallery';
		frame.content.get('view').sidebar.unset('gallery'); // Hide Gallery Settings in sidebar

		// When we are editing a gallery
		overrideGalleryInsert();
		frame.on( 'toolbar:render:gallery-edit', function() {
			overrideGalleryInsert();
		});

		frame.on( 'content:render:browse', function( browser ) {
			if ( !browser ) return;
			// Hide Gallery Setting in sidebar
			browser.sidebar.on('ready', function(){
				browser.sidebar.unset('gallery');
			});
			// Hide filter/search as they don't work
				browser.toolbar.on('ready', function(){
					if(browser.toolbar.controller._state == 'gallery-library'){
						browser.toolbar.$el.hide();
					}
				});
		});

		// All images removed
		frame.state().get('library').on( 'remove', function() {
			var models = frame.state().get('library');
			if(models.length == 0){
				selection = false;
				$.post(ajaxurl, { ids: '', action: 'save_gallery_images', post_id: $('#post_ID').val(), nonce: $('#cfpf-format-gallery-nonce-field').val() });
			}
		});

		// Override insert button
		function overrideGalleryInsert() {
			frame.toolbar.get('view').set({
				insert: {
					style: 'primary',
					text: 'Create Gallery',

					click: function() {
						var models = frame.state().get('library'),
							ids = '';
							items = '';

						models.each( function( attachment ) {
							ids += attachment.id + ',';
							image_thumb = attachment.attributes.sizes.thumbnail || 0;
							if (typeof image_thumb == 'object') {
								img_width = image_thumb.width;
								img_height = image_thumb.height;
								img_url = image_thumb.url;
							} else {
								img_width = '150';
								img_height = '150';
								img_url = attachment.attributes.url;
							}
							items += '<li><img src="'+ img_url +'" height="'+ img_height +'" width="'+ img_width +'" ></li>';
						});

						ids = ids.substring(0, ids.length - 1); // trim that last comma
						this.el.innerHTML = 'Working...';
						selection = loadImages(ids);
						frame.close();
						$('#cfpf-format-gallery-ids-field').val(ids);
						$('#post-format-gallery-items').html('<ul class="gallery">' + items + '</ul>');
					}
				}
			});
		}
	});

	// Load images
	function loadImages(images) {
		if( images ){
			var shortcode = new wp.shortcode({
				tag:    'gallery',
				attrs:   { ids: images },
				type:   'single'
			});

			console.log('Shortcode: ', shortcode);

			var attachments = wp.media.gallery.attachments( shortcode );

			console.log('Attachments: ', attachments);

			var selection = new wp.media.model.Selection( attachments.models, {
				props:    attachments.props.toJSON(),
				multiple: true
			});

			console.log('Selection: ', selection);
			selection.gallery = attachments.gallery;

			// Fetch the query's attachments, and then break ties from the
			// query to allow for sorting.
			selection.more().done( function() {
				// Break ties with the query.
				selection.props.set({ query: false });
				selection.unmirror();
				selection.props.unset('orderby');
			});

			console.log('Selection props: ', selection.props);
			return selection;
		}

		return false;
	}

	function dates_validation() {
		var start_date = Math.round( new Date($('.political-options-datepicker[name="event_date_start"]').datepicker( "getDate" )).getTime()/1000 );
		var end_date = 	 Math.round( new Date($('.political-options-datepicker[name="event_date_end"]').datepicker( "getDate" )).getTime()/1000 );
		var event_time_start = $('select[name="event_time_start"]').val();

		if((event_time_start == '0:01' || event_time_start == '00:01') && ! end_date) {
				$('span.event-wrong-dates').text('');
				$('#publish').removeAttr('disabled');			
		} else {
			if(end_date < start_date) {
				$('span.event-wrong-dates').text(dp_translations.dates_validation_message);
				$('#publish').attr('disabled', 'disabled');
			}
			else {
				$('span.event-wrong-dates').text('');
				$('#publish').removeAttr('disabled');
			}
		}	
	}	

});