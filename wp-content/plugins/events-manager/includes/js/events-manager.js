jQuery(document).ready( function($){
	var load_ui_css = false; //load jquery ui css?
	/* Time Entry */
	$('#start-time').each(function(i, el){
		$(el).addClass('em-time-input em-time-start').next('#end-time').addClass('em-time-input em-time-end').parent().addClass('em-time-range');
	});
	if( $(".em-time-input").length > 0 ){
		em_setup_timepicker('body');
	}
	/* Calendar AJAX */
	$('.em-calendar-wrapper a').off("click");
	$('.em-calendar-wrapper').on('click', 'a.em-calnav, a.em-calnav', function(e){
		e.preventDefault();
		$(this).closest('.em-calendar-wrapper').prepend('<div class="loading" id="em-loading"></div>');
		var url = em_ajaxify($(this).attr('href'));
		$(this).closest('.em-calendar-wrapper').load(url, function(){$(this).trigger('em_calendar_load');});
	} );

	//Events Search
	$(document).on('click change', '.em-toggle', function(e){
		e.preventDefault();
		//show or hide advanced tickets, hidden by default
		var el = $(this);
		var rel = el.attr('rel').split(':');
		if( el.hasClass('show-search') ){
			if( rel.length > 1 ){ el.closest(rel[1]).find(rel[0]).slideUp(); }
			else{ $(rel[0]).slideUp(); }
			el.find('.show, .show-advanced').show();
			el.find('.hide, .hide-advanced').hide();
			el.removeClass('show-search');
		}else{
			if( rel.length > 1 ){ el.closest(rel[1]).find(rel[0]).slideDown(); }
			else{ $(rel[0]).slideDown(); }
			el.find('.show, .show-advanced').hide();
			el.find('.hide, .hide-advanced').show();
			el.addClass('show-search');
		}
		
	});
	if( EM.search_term_placeholder ){
		if( 'placeholder' in document.createElement('input') ){
			$('input.em-events-search-text, input.em-search-text').attr('placeholder', EM.search_term_placeholder);
		}else{
			$('input.em-events-search-text, input.em-search-text').on('blur', function(){
				if( this.value=='' ) this.value = EM.search_term_placeholder;
			}).on('focus', function(){
				if( this.value == EM.search_term_placeholder ) this.value='';
			}).trigger('blur');
		}
	}
	$('.em-search-form select[name=country]').on('change', function(){
		var el = $(this);
		$('.em-search select[name=state]').html('<option value="">'+EM.txt_loading+'</option>');
		$('.em-search select[name=region]').html('<option value="">'+EM.txt_loading+'</option>');
		$('.em-search select[name=town]').html('<option value="">'+EM.txt_loading+'</option>');
		if( el.val() != '' ){
			el.closest('.em-search-location').find('.em-search-location-meta').slideDown();
			var data = {
				action : 'search_states',
				country : el.val(),
				return_html : true
			};
			$('.em-search select[name=state]').load( EM.ajaxurl, data );
			data.action = 'search_regions';
			$('.em-search select[name=region]').load( EM.ajaxurl, data );
			data.action = 'search_towns';
			$('.em-search select[name=town]').load( EM.ajaxurl, data );
		}else{
			el.closest('.em-search-location').find('.em-search-location-meta').slideUp();
		}
	});

	$('.em-search-form select[name=region]').on('change', function(){
		$('.em-search select[name=state]').html('<option value="">'+EM.txt_loading+'</option>');
		$('.em-search select[name=town]').html('<option value="">'+EM.txt_loading+'</option>');
		var data = {
			action : 'search_states',
			region : $(this).val(),
			country : $('.em-search-form select[name=country]').val(),
			return_html : true
		};
		$('.em-search select[name=state]').load( EM.ajaxurl, data );
		data.action = 'search_towns';
		$('.em-search select[name=town]').load( EM.ajaxurl, data );
	});

	$('.em-search-form select[name=state]').on('change', function(){
		$('.em-search select[name=town]').html('<option value="">'+EM.txt_loading+'</option>');
		var data = {
			action : 'search_towns',
			state : $(this).val(),
			region : $('.em-search-form select[name=region]').val(),
			country : $('.em-search-form select[name=country]').val(),
			return_html : true
		};
		$('.em-search select[name=town]').load( EM.ajaxurl, data );
	});
	
	//in order for this to work, you need the above classes to be present in your templates
	$(document).on('submit', '.em-search-form, .em-events-search-form', function(e){
		var form = $(this);
    	if( this.em_search && this.em_search.value == EM.txt_search){ this.em_search.value = ''; }
    	var results_wrapper = form.closest('.em-search-wrapper').find('.em-search-ajax');
    	if( results_wrapper.length == 0 ) results_wrapper = $('.em-search-ajax');
    	if( results_wrapper.length > 0 ){
    		results_wrapper.append('<div class="loading" id="em-loading"></div>');
    		var submitButton = form.find('.em-search-submit');
    		submitButton.data('buttonText', submitButton.val()).val(EM.txt_searching);
    		var img = submitButton.children('img');
    		if( img.length > 0 ) img.attr('src', img.attr('src').replace('search-mag.png', 'search-loading.gif'));
    		var vars = form.serialize();
    		$.ajax( EM.ajaxurl, {
				type : 'POST',
	    		dataType : 'html',
	    		data : vars,
			    success : function(responseText){
			    	submitButton.val(submitButton.data('buttonText'));
			    	if( img.length > 0 ) img.attr('src', img.attr('src').replace('search-loading.gif', 'search-mag.png'));
		    		results_wrapper.replaceWith(responseText);
		        	if( form.find('input[name=em_search]').val() == '' ){ form.find('input[name=em_search]').val(EM.txt_search); }
		        	//reload results_wrapper
		        	results_wrapper = form.closest('.em-search-wrapper').find('.em-search-ajax');
		        	if( results_wrapper.length == 0 ) results_wrapper = $('.em-search-ajax');
			    	jQuery(document).triggerHandler('em_search_ajax', [vars, results_wrapper, e]); //ajax has loaded new results
			    }
	    	});
    		e.preventDefault();
			return false;
    	}
	});
	if( $('.em-search-ajax').length > 0 ){
		$(document).on('click', '.em-search-ajax a.page-numbers', function(e){
			var a = $(this);
			var data = a.closest('.em-pagination').attr('data-em-ajax');
			var wrapper = a.closest('.em-search-ajax');
			var wrapper_parent = wrapper.parent();
		    var qvars = a.attr('href').split('?');
		    var vars = qvars[1];
		    //add data-em-ajax att if it exists
		    if( data != '' ){
		    	vars = vars != '' ? vars+'&'+data : data;
		    }
		    wrapper.append('<div class="loading" id="em-loading"></div>');
		    $.ajax( EM.ajaxurl, {
				type : 'POST',
	    		dataType : 'html',
	    		data : vars,
			    success : function(responseText) {
			    	wrapper.replaceWith(responseText);
			    	wrapper = wrapper_parent.find('.em-search-ajax');
			    	jQuery(document).triggerHandler('em_search_ajax', [vars, wrapper, e]); //ajax has loaded new results
			    }
	    	});
			e.preventDefault();
			return false;
		});
	}
		
	/*
	 * ADMIN AREA AND PUBLIC FORMS (Still polishing this section up, note that form ids and classes may change accordingly)
	 */
	//Events List
		//Approve/Reject Links
		$('.events-table').on('click', '.em-event-delete', function(){
			if( !confirm("Are you sure you want to delete?") ){ return false; }
			window.location.href = this.href;
		});
	//Forms
	$('#event-form #event-image-delete, #location-form #location-image-delete').on('click', function(){
		var el = $(this);
		if( el.is(':checked') ){
			el.closest('.event-form-image, .location-form-image').find('#event-image-img, #location-image-img').hide();
		}else{
			el.closest('.event-form-image, .location-form-image').find('#event-image-img, #location-image-img').show();
		}
	});
	//Event Editor 
		//Recurrence Warnings
		$('#event-form.em-event-admin-recurring').on('submit', function(event){
			var form = $(this);
			if( form.find('input[name="event_reschedule"]').first().val() == 1 ){
				var warning_text = EM.event_reschedule_warning;
			}else if( form.find('input[name="event_recreate_tickets"]').first().val() == 1 ){
				var warning_text = EM.event_recurrence_bookings;
			}else{
				var warning_text = EM.event_recurrence_overwrite;
			}
			confirmation = confirm(warning_text);
			if( confirmation == false ){
				event.preventDefault();
			}
		});
		//Buttons for recurrence warnings within event editor forms
		$('.em-reschedule-trigger').on('click', function(e){
			e.preventDefault();
			var trigger = $(this);
			trigger.closest('.em-recurrence-reschedule').find(trigger.data('target')).removeClass('reschedule-hidden');
			trigger.siblings('.em-reschedule-value').val(1);
			trigger.addClass('reschedule-hidden').siblings('a').removeClass('reschedule-hidden');
		});
		$('.em-reschedule-cancel').on('click', function(e){
			e.preventDefault();
			var trigger = $(this);
			trigger.closest('.em-recurrence-reschedule').find(trigger.data('target')).addClass('reschedule-hidden');
			trigger.siblings('.em-reschedule-value').val(0);
			trigger.addClass('reschedule-hidden').siblings('a').removeClass('reschedule-hidden');
		});
	//Tickets & Bookings
	if( $("#em-tickets-form").length > 0 ){
		//Enable/Disable Bookings
		$('#event-rsvp').on('click', function(event){
			if( !this.checked ){
				confirmation = confirm(EM.disable_bookings_warning);
				if( confirmation == false ){
					event.preventDefault();
				}else{
					$('#event-rsvp-options').hide();
				}
			}else{
				$('#event-rsvp-options').fadeIn();
			}
		});
		if($('input#event-rsvp').is(":checked")) {
			$("div#rsvp-data").fadeIn();
		} else {
			$("div#rsvp-data").hide();
		}
		//Ticket(s) UI
		var reset_ticket_forms = function(){
			$('#em-tickets-form table tbody tr.em-tickets-row').show();
			$('#em-tickets-form table tbody tr.em-tickets-row-form').hide();
		};
		//recurrences and cut-off logic for ticket availability
		if( $('#em-recurrence-checkbox').length > 0 ){
			$('#em-recurrence-checkbox').on('change', function(){
				if( $('#em-recurrence-checkbox').is(':checked') ){
					$('#em-tickets-form .ticket-dates-from-recurring, #em-tickets-form .ticket-dates-to-recurring, #event-rsvp-options .em-booking-date-recurring').show();
					$('#em-tickets-form .ticket-dates-from-normal, #em-tickets-form .ticket-dates-to-normal, #event-rsvp-options .em-booking-date-normal, #em-tickets-form .hidden').hide();
				}else{
					$('#em-tickets-form .ticket-dates-from-normal, #em-tickets-form .ticket-dates-to-normal, #event-rsvp-options .em-booking-date-normal').show();
					$('#em-tickets-form .ticket-dates-from-recurring, #em-tickets-form .ticket-dates-to-recurring, #event-rsvp-options .em-booking-date-recurring, #em-tickets-form .hidden').hide();
				}
			}).trigger('change');
		}else if( $('#em-form-recurrence').length > 0 ){
			$('#em-tickets-form .ticket-dates-from-recurring, #em-tickets-form .ticket-dates-to-recurring, #event-rsvp-options .em-booking-date-recurring').show();
			$('#em-tickets-form .ticket-dates-from-normal, #em-tickets-form .ticket-dates-to-normal, #event-rsvp-options .em-booking-date-normal, #em-tickets-form .hidden').hide();
		}else{
			$('#em-tickets-form .ticket-dates-from-recurring, #em-tickets-form .ticket-dates-to-recurring, #event-rsvp-options .em-booking-date-recurring, #em-tickets-form .hidden').hide();
		}
		//Add a new ticket
		$("#em-tickets-add").on('click', function(e){
			e.preventDefault();
			reset_ticket_forms();
			//create copy of template slot, insert so ready for population
			var tickets = $('#em-tickets-form table tbody');
			var rowNo = tickets.length+1;
			var slot = tickets.first('.em-ticket-template').clone(true).attr('id','em-ticket-'+ rowNo).removeClass('em-ticket-template').addClass('em-ticket').appendTo($('#em-tickets-form table'));
			//change the index of the form element names
			slot.find('*[name]').each( function(index,el){
				el = $(el);
				el.attr('name', el.attr('name').replace('em_tickets[0]','em_tickets['+rowNo+']'));
			});
			//show ticket and switch to editor
			slot.show().find('.ticket-actions-edit').trigger('click');
			//refresh datepicker and values
			slot.find('.em-date-input-loc').datepicker('destroy').removeAttr('id'); //clear all datepickers
			slot.find('.em-time-input').off().each(function(index, el){
				if( typeof this.em_timepickerObj == 'object' ){
					this.em_timepicker('remove');
				}
			}); //clear all em_timepickers - consequently, also other click/blur/change events, recreate the further down
			em_setup_datepicker(slot);
			em_setup_timepicker(slot);
		    $('html, body').animate({ scrollTop: slot.offset().top - 30 }); //sends user to form
			check_ticket_sortability();
		});
		//Edit a Ticket
		$(document).on('click', '.ticket-actions-edit', function(e){
			e.preventDefault();
			reset_ticket_forms();
			var tbody = $(this).closest('tbody');
			tbody.find('tr.em-tickets-row').hide();
			tbody.find('tr.em-tickets-row-form').fadeIn();
			return false;
		});
		$(document).on('click', '.ticket-actions-edited', function(e){
			e.preventDefault();
			var tbody = $(this).closest('tbody');
			var rowNo = tbody.attr('id').replace('em-ticket-','');
			tbody.find('.em-tickets-row').fadeIn();
			tbody.find('.em-tickets-row-form').hide();
			tbody.find('*[name]').each(function(index,el){
				el = $(el);
				if( el.attr('name') == 'ticket_start_pub'){
					tbody.find('span.ticket_start').text(el.val());
				}else if( el.attr('name') == 'ticket_end_pub' ){
					tbody.find('span.ticket_end').text(el.val());
				}else if( el.attr('name') == 'em_tickets['+rowNo+'][ticket_type]' ){
					if( el.find(':selected').val() == 'members' ){
						tbody.find('span.ticket_name').prepend('* ');
					}
				}else if( el.attr('name') == 'em_tickets['+rowNo+'][ticket_start_recurring_days]' ){
					var text = tbody.find('select.ticket-dates-from-recurring-when').val() == 'before' ? '-'+el.val():el.val();
					if( el.val() != '' ){
						tbody.find('span.ticket_start_recurring_days').text(text);
						tbody.find('span.ticket_start_recurring_days_text, span.ticket_start_time').removeClass('hidden').show();
					}else{
						tbody.find('span.ticket_start_recurring_days').text(' - ');
						tbody.find('span.ticket_start_recurring_days_text, span.ticket_start_time').removeClass('hidden').hide();
					}
				}else if( el.attr('name') == 'em_tickets['+rowNo+'][ticket_end_recurring_days]' ){
					var text = tbody.find('select.ticket-dates-to-recurring-when').val() == 'before' ? '-'+el.val():el.val();
					if( el.val() != '' ){
						tbody.find('span.ticket_end_recurring_days').text(text);
						tbody.find('span.ticket_end_recurring_days_text, span.ticket_end_time').removeClass('hidden').show();
					}else{
						tbody.find('span.ticket_end_recurring_days').text(' - ');
						tbody.find('span.ticket_end_recurring_days_text, span.ticket_end_time').removeClass('hidden').hide();
					}
				}else{
					var classname = el.attr('name').replace('em_tickets['+rowNo+'][','').replace(']','').replace('[]','');
					tbody.find('.em-tickets-row .'+classname).text(el.val());
				}
			});
			//allow for others to hook into this
			$(document).triggerHandler('em_maps_tickets_edit', [tbody, rowNo, true]);
		    $('html, body').animate({ scrollTop: tbody.parent().offset().top - 30 }); //sends user back to top of form
			return false;
		});
		$(document).on('change', '.em-ticket-form select.ticket_type', function(e){
			//check if ticket is for all users or members, if members, show roles to limit the ticket to
			var el = $(this);
			if( el.find('option:selected').val() == 'members' ){
				el.closest('.em-ticket-form').find('.ticket-roles').fadeIn();
			}else{
				el.closest('.em-ticket-form').find('.ticket-roles').hide();
			}
		});
		$(document).on('click', '.em-ticket-form .ticket-options-advanced', function(e){
			//show or hide advanced tickets, hidden by default
			e.preventDefault();
			var el = $(this);
			if( el.hasClass('show') ){
				el.closest('.em-ticket-form').find('.em-ticket-form-advanced').fadeIn();
				el.find('.show,.show-advanced').hide();
				el.find('.hide,.hide-advanced').show();
			}else{
				el.closest('.em-ticket-form').find('.em-ticket-form-advanced').hide();
				el.find('.show,.show-advanced').show();
				el.find('.hide,.hide-advanced').hide();
			}
			el.toggleClass('show');
		});
		$('.em-ticket-form').each( function(){
			//check whether to show advanced options or not by default for each ticket
			var show_advanced = false;
			var el = $(this); 
			el.find('.em-ticket-form-advanced input[type="text"]').each(function(){ if(this.value != '') show_advanced = true; });
			if( el.find('.em-ticket-form-advanced input[type="checkbox"]:checked').length > 0 ){ show_advanced = true; }
			el.find('.em-ticket-form-advanced option:selected').each(function(){ if(this.value != '') show_advanced = true; });
			if( show_advanced ) el.find('.ticket-options-advanced').trigger('click');
		});
		//Delete a ticket
		$(document).on('click', '.ticket-actions-delete', function(e){
			e.preventDefault();
			var el = $(this);
			var tbody = el.closest('tbody');
			if( tbody.find('input.ticket_id').val() > 0 ){
				//only will happen if no bookings made
				el.text('Deleting...');	
				$.getJSON( $(this).attr('href'), {'em_ajax_action':'delete_ticket', 'id':tbody.find('input.ticket_id').val()}, function(data){
					if(data.result){
						tbody.remove();
					}else{
						el.text('Delete');
						alert(data.error);
					}
				});
			}else{
				//not saved to db yet, so just remove
				tbody.remove();
			}
			check_ticket_sortability();
			return false;
		});
		//Sort Tickets
		$('#em-tickets-form.em-tickets-sortable table').sortable({
			items: '> tbody',
			placeholder: "em-ticket-sortable-placeholder",
			handle:'.ticket-status',
			helper: function( event, el ){
				var helper = $(el).clone().addClass('em-ticket-sortable-helper');
				var tds = helper.find('.em-tickets-row td').length;
				helper.children().remove();
				helper.append('<tr class="em-tickets-row"><td colspan="'+tds+'" style="text-align:left; padding-left:15px;"><span class="dashicons dashicons-tickets-alt"></span></td></tr>');
				return helper;
			},
		});
		var check_ticket_sortability = function(){
			var em_tickets = $('#em-tickets-form table tbody.em-ticket');
			if( em_tickets.length == 1 ){
				em_tickets.find('.ticket-status').addClass('single');
				$('#em-tickets-form.em-tickets-sortable table').sortable( "option", "disabled", true );
			}else{
				em_tickets.find('.ticket-status').removeClass('single');
				$('#em-tickets-form.em-tickets-sortable table').sortable( "option", "disabled", false );
			}
		};
		check_ticket_sortability();
	}
	//Manageing Bookings
	if( $('#em-bookings-table').length > 0 ){
		//Pagination link clicks
		$(document).on('click', '#em-bookings-table .tablenav-pages a', function(){
			var el = $(this);
			var form = el.parents('#em-bookings-table form.bookings-filter');
			//get page no from url, change page, submit form
			var match = el.attr('href').match(/#[0-9]+/);
			if( match != null && match.length > 0){
				var pno = match[0].replace('#','');
				form.find('input[name=pno]').val(pno);
			}else{
				form.find('input[name=pno]').val(1);
			}
			form.trigger('submit');
			return false;
		});
		//Overlay Options
		var em_bookings_settings_dialog = {
			modal : true,
			autoOpen: false,
			minWidth: 500,
			height: 'auto',
			buttons: [{
				text: EM.bookings_settings_save,
				click: function(e){
					e.preventDefault();
					//we know we'll deal with cols, so wipe hidden value from main
					var match = $("#em-bookings-table form.bookings-filter [name=cols]").val('');
					var booking_form_cols = $('form#em-bookings-table-settings-form input.em-bookings-col-item');
					$.each( booking_form_cols, function(i,item_match){
						//item_match = $(item_match);
						if( item_match.value == 1 ){
							if( match.val() != ''){
								match.val(match.val()+','+item_match.name);
							}else{
								match.val(item_match.name);
							}
						}
					});
					//submit main form
					$('#em-bookings-table-settings').trigger('submitted'); //hook into this with bind()
					$('#em-bookings-table form.bookings-filter').trigger('submit');					
					$(this).dialog('close');
				}
			}]
		};
		var em_bookings_export_dialog = {
			modal : true,
			autoOpen: false,
			minWidth: 500,
			height: 'auto',
			buttons: [{
				text: EM.bookings_export_save,
				click: function(e){
					$(this).children('form').trigger('submit');
					$(this).dialog('close');
				}
			}]
		};
		if( $("#em-bookings-table-settings").length > 0 ){
			//Settings Overlay
			$("#em-bookings-table-settings").dialog(em_bookings_settings_dialog);
			$(document).on('click', '#em-bookings-table-settings-trigger', function(e){ e.preventDefault(); $("#em-bookings-table-settings").dialog('open'); });
			//Export Overlay
			$("#em-bookings-table-export").dialog(em_bookings_export_dialog);
			$(document).on('click', '#em-bookings-table-export-trigger', function(e){ e.preventDefault(); $("#em-bookings-table-export").dialog('open'); });
			var export_overlay_show_tickets = function(){
				if( $('#em-bookings-table-export-form input[name=show_tickets]').is(':checked') ){
					$('#em-bookings-table-export-form .em-bookings-col-item-ticket').show();
					$('#em-bookings-table-export-form #em-bookings-export-cols-active .em-bookings-col-item-ticket input').val(1);
				}else{
					$('#em-bookings-table-export-form .em-bookings-col-item-ticket').hide().find('input').val(0);					
				}
			};
			//Sync export overlay with table search field changes
			$('#em-bookings-table form select').each(function(i, el){
				$(el).on('change', function(e){
					var select_el = $(this);
					var input_par = $('#em-bookings-table-export-form input[name='+select_el.attr('name')+']');
					var input_par_selected = select_el.find('option:selected');
					input_par.val(input_par_selected.val());
				});
			});
			
			export_overlay_show_tickets();
			$('#em-bookings-table-export-form input[name=show_tickets]').on('click', export_overlay_show_tickets);
			//Sortables
			$( ".em-bookings-cols-sortable" ).sortable({
				connectWith: ".em-bookings-cols-sortable",
				update: function(event, ui) {
					if( ui.item.parents('ul#em-bookings-cols-active, ul#em-bookings-export-cols-active').length > 0 ){							
						ui.item.addClass('ui-state-highlight').removeClass('ui-state-default').children('input').val(1);
					}else{
						ui.item.addClass('ui-state-default').removeClass('ui-state-highlight').children('input').val(0);
					}
				}
			}).disableSelection();
			load_ui_css = true;
		}
		//Widgets and filter submissions
		$(document).on('submit', '#em-bookings-table form.bookings-filter', function(e){
			var el = $(this);			
			//append loading spinner
			el.parents('#em-bookings-table').find('.table-wrap').first().append('<div id="em-loading" />');
			//ajax call
			$.post( EM.ajaxurl, el.serializeArray(), function(data){
				var root = el.parents('#em-bookings-table').first();
				root.replaceWith(data);
				//recreate overlays
				$('#em-bookings-table-export input[name=scope]').val(root.find('select[name=scope]').val());
				$('#em-bookings-table-export input[name=status]').val(root.find('select[name=status]').val());
				jQuery(document).triggerHandler('em_bookings_filtered', [data, root, el]);
			});
			return false;
		});
		//Approve/Reject Links
		$(document).on('click', '.em-bookings-approve,.em-bookings-reject,.em-bookings-unapprove,.em-bookings-delete', function(){
			var el = $(this); 
			if( el.hasClass('em-bookings-delete') ){
				if( !confirm(EM.booking_delete) ){ return false; }
			}
			var url = em_ajaxify( el.attr('href'));		
			var td = el.parents('td').first();
			td.html(EM.txt_loading);
			td.load( url );
			return false;
		});
	}
	//Old Bookings Table - depreciating soon
	if( $('.em_bookings_events_table').length > 0 ){
		//Widgets and filter submissions
		$(document).on('submit', '.em_bookings_events_table form', function(e){
			var el = $(this);
			var url = em_ajaxify( el.attr('action') );		
			el.parents('.em_bookings_events_table').find('.table-wrap').first().append('<div id="em-loading" />');
			$.get( url, el.serializeArray(), function(data){
				el.parents('.em_bookings_events_table').first().replaceWith(data);
			});
			return false;
		});
		//Pagination link clicks
		$(document).on('click', '.em_bookings_events_table .tablenav-pages a', function(){
			var el = $(this);
			var url = em_ajaxify( el.attr('href') );	
			el.parents('.em_bookings_events_table').find('.table-wrap').first().append('<div id="em-loading" />');
			$.get( url, function(data){
				el.parents('.em_bookings_events_table').first().replaceWith(data);
			});
			return false;
		});
	}
	
	//Manual Booking
	$(document).on('click', 'a.em-booking-button', function(e){
		e.preventDefault();
		var button = $(this);
		if( button.text() != EM.bb_booked && $(this).text() != EM.bb_booking){
			button.text(EM.bb_booking);
			var button_data = button.attr('id').split('_'); 
			$.ajax({
				url: EM.ajaxurl,
				dataType: 'jsonp',
				data: {
					event_id : button_data[1],
					_wpnonce : button_data[2],
					action : 'booking_add_one'
				},
				success : function(response, statusText, xhr, $form) {
					if(response.result){
						button.text(EM.bb_booked);
					}else{
						button.text(EM.bb_error);
					}
					if(response.message != '') alert(response.message);
					$(document).triggerHandler('em_booking_button_response', [response, button]);
				},
				error : function(){ button.text(EM.bb_error); }
			});
		}
		return false;
	});	
	$(document).on('click', 'a.em-cancel-button', function(e){
		e.preventDefault();
		var button = $(this);
		if( button.text() != EM.bb_cancelled && button.text() != EM.bb_canceling){
			button.text(EM.bb_canceling);
			var button_data = button.attr('id').split('_'); 
			$.ajax({
				url: EM.ajaxurl,
				dataType: 'jsonp',
				data: {
					booking_id : button_data[1],
					_wpnonce : button_data[2],
					action : 'booking_cancel'
				},
				success : function(response, statusText, xhr, $form) {
					if(response.result){
						button.text(EM.bb_cancelled);
					}else{
						button.text(EM.bb_cancel_error);
					}
				},
				error : function(){ button.text(EM.bb_cancel_error); }
			});
		}
		return false;
	});  

	//Datepicker
	if( $('.em-date-single, .em-date-range, #em-date-start').length > 0 ){
		load_ui_css = true;
		em_setup_datepicker('body');
	}
	if( load_ui_css ) em_load_jquery_css();
	
	//previously in em-admin.php
	function updateIntervalDescriptor () { 
		$(".interval-desc").hide();
		var number = "-plural";
		if ($('input#recurrence-interval').val() == 1 || $('input#recurrence-interval').val() == "")
		number = "-singular";
		var descriptor = "span#interval-"+$("select#recurrence-frequency").val()+number;
		$(descriptor).show();
	}
	function updateIntervalSelectors () {
		$('p.alternate-selector').hide();   
		$('p#'+ $('select#recurrence-frequency').val() + "-selector").show();
	}
	function updateShowHideRecurrence () {
		if( $('input#event-recurrence').is(":checked")) {
			$("#event_recurrence_pattern").fadeIn();
			$("#event-date-explanation").hide();
			$("#recurrence-dates-explanation").show();
			$("h3#recurrence-dates-title").show();
			$("h3#event-date-title").hide();     
		} else {
			$("#event_recurrence_pattern").hide();
			$("#recurrence-dates-explanation").hide();
			$("#event-date-explanation").show();
			$("h3#recurrence-dates-title").hide();
			$("h3#event-date-title").show();   
		}
	}		 
	$("#recurrence-dates-explanation").hide();
	$("#date-to-submit").hide();
	$("#end-date-to-submit").hide();
	
	$("#localised-date").show();
	$("#localised-end-date").show();
	
	$('#em-wrapper input.select-all').on('change', function(){
	 	if($(this).is(':checked')){
			$('input.row-selector').prop('checked', true);
			$('input.select-all').prop('checked', true);
	 	}else{
			$('input.row-selector').prop('checked', false);
			$('input.select-all').prop('checked', false);
		}
	}); 
	
	updateIntervalDescriptor(); 
	updateIntervalSelectors();
	updateShowHideRecurrence();
	$('input#event-recurrence').on('change', updateShowHideRecurrence);
	   
	// recurrency elements   
	$('input#recurrence-interval').on('keyup', updateIntervalDescriptor);
	$('select#recurrence-frequency').on('change', updateIntervalDescriptor);
	$('select#recurrence-frequency').on('change', updateIntervalSelectors);

	/* Load any maps */	
	if( $('.em-location-map').length > 0 || $('.em-locations-map').length > 0 || $('#em-map').length > 0 || $('.em-search-geo').length > 0 ){
		em_maps_load();
	}

	/* Location Type Selection */
	$('.em-location-types .em-location-types-select').on('change', function(){
		let el = $(this);
		if( el.val() == 0 ){
			$('.em-location-type').hide();
		}else{
			let location_type = el.find('option:selected').data('display-class');
			$('.em-location-type').hide();
			$('.em-location-type.'+location_type).show();
			if( location_type != 'em-location-type-place' ){
				jQuery('#em-location-reset a').trigger('click');
			}
		}
		if( el.data('active') !== '' && el.val() !== el.data('active') ){
			$('.em-location-type-delete-active-alert').hide();
			$('.em-location-type-delete-active-alert').show();
		}else{
			$('.em-location-type-delete-active-alert').hide();
		}
	}).trigger('change');
	
	//Finally, add autocomplete here
	//Autocomplete
	if( jQuery( "div.em-location-data input#location-name" ).length > 0 ){
		jQuery( "div.em-location-data input#location-name" ).autocomplete({
			source: EM.locationajaxurl,
			minLength: 2,
			focus: function( event, ui ){
				jQuery("input#location-id" ).val( ui.item.value );
				return false;
			},			 
			select: function( event, ui ){
				jQuery("input#location-id" ).val(ui.item.id).trigger('change');
				jQuery("input#location-name" ).val(ui.item.value);
				jQuery('input#location-address').val(ui.item.address);
				jQuery('input#location-town').val(ui.item.town);
				jQuery('input#location-state').val(ui.item.state);
				jQuery('input#location-region').val(ui.item.region);
				jQuery('input#location-postcode').val(ui.item.postcode);
				jQuery('input#location-latitude').val(ui.item.latitude);
				jQuery('input#location-longitude').val(ui.item.longitude);
				if( ui.item.country == '' ){
					jQuery('select#location-country option:selected').removeAttr('selected');
				}else{
					jQuery('select#location-country option[value="'+ui.item.country+'"]').attr('selected', 'selected');
				}
				jQuery('div.em-location-data input').css('background-color','#ccc').prop('readonly', true);
				jQuery('div.em-location-data select').css('background-color','#ccc').css('color', '#666666').prop('disabled', true);
				jQuery('#em-location-reset').show();
				jQuery('#em-location-search-tip').hide();
				jQuery(document).triggerHandler('em_locations_autocomplete_selected', [event, ui]);
				return false;
			}
		}).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
			html_val = "<a>" + em_esc_attr(item.label) + '<br><span style="font-size:11px"><em>'+ em_esc_attr(item.address) + ', ' + em_esc_attr(item.town)+"</em></span></a>";
			return jQuery( "<li></li>" ).data( "item.autocomplete", item ).append(html_val).appendTo( ul );
		};
		jQuery('#em-location-reset a').on('click', function(){
			jQuery('div.em-location-data input').css('background-color','#fff').val('').prop('readonly', false);
			jQuery('div.em-location-data select').css('background-color','#fff').css('color', 'auto').prop('disabled', false);
			jQuery('div.em-location-data option:selected').removeAttr('selected');
			jQuery('input#location-id').val('');
			jQuery('#em-location-reset').hide();
			jQuery('#em-location-search-tip').show();
			jQuery('#em-map').hide();
			jQuery('#em-map-404').show();
			if(typeof(marker) !== 'undefined'){
				marker.setPosition(new google.maps.LatLng(0, 0));
				infoWindow.close();
				marker.setDraggable(true);
			}
			return false;
		});
		if( jQuery('input#location-id').val() != '0' && jQuery('input#location-id').val() != '' ){
			jQuery('div.em-location-data input').css('background-color','#ccc').prop('readonly', true);
			jQuery('div.em-location-data select').css('background-color','#ccc').css('color', '#666666').prop('disabled', true);
			jQuery('#em-location-reset').show();
			jQuery('#em-location-search-tip').hide();
		}
	}
	/* Local JS Timezone related placeholders */
	/* Moment JS Timzeone PH */
	if( window.moment ){
		var replace_specials = function( day, string ){
			// replace things not supported by moment
			string = string.replace(/##T/g, Intl.DateTimeFormat().resolvedOptions().timeZone);
			string = string.replace(/#T/g, "GMT"+day.format('Z'));
			string = string.replace(/###t/g, day.utcOffset()*-60);
			string = string.replace(/##t/g, day.isDST());
			string = string.replace(/#t/g, day.daysInMonth());
			return string;
		};
		$('.em-date-momentjs').each( function(){
			// Start Date
			var el = $(this);
			var day_start = moment.unix(el.data('date-start'));
			var date_start_string = replace_specials(day_start, day_start.format(el.data('date-format')));
			if( el.data('date-start') !== el.data('date-end') ){
				// End Date
				var day_end = moment.unix(el.data('date-end'));
				var day_end_string = replace_specials(day_start, day_end.format(el.data('date-format')));
				// Output
				var date_string = date_start_string + el.data('date-separator') + day_end_string;
			}else{
				var date_string = date_start_string;
			}
			el.text(date_string);
		});
		var get_date_string = function(ts, format){
			let date = new Date(ts * 1000);
			let minutes = date.getMinutes();
			if( format == 24 ){
				let hours = date.getHours();
				hours = hours < 10 ? '0' + hours : hours;
				minutes = minutes < 10 ? '0' + minutes : minutes;
				return hours + ':' + minutes;
			}else{
				let hours = date.getHours() % 12;
				let ampm = hours >= 12 ? 'PM' : 'AM';
				if( hours === 0 ) hours = 12; // the hour '0' should be '12'
				minutes = minutes < 10 ? '0'+minutes : minutes;
				return hours + ':' + minutes + ' ' + ampm;
			}
		}
		$('.em-time-localjs').each( function(){
			var el = $(this);
			var strTime = get_date_string( el.data('time'), el.data('time-format') );
			if( el.data('time-end') ){
				var separator = el.data('time-separator') ? el.data('time-separator') : ' - ';
				strTime = strTime + separator + get_date_string( el.data('time-end'), el.data('time-format') );
			}
			el.text(strTime);
		});
	}
	/* Done! */
	jQuery(document).triggerHandler('em_javascript_loaded');
});

function em_load_jquery_css(){
	if( EM.ui_css && jQuery('link#jquery-ui-css').length == 0 ){
		var script = document.createElement("link");
		script.id = 'jquery-ui-css';
		script.rel = "stylesheet";
		script.href = EM.ui_css;
		document.body.appendChild(script);
	}
}

function em_setup_datepicker(wrap){	
	wrap = jQuery(wrap);
	//default picker vals
	var datepicker_vals = { altFormat: "yy-mm-dd", changeMonth: true, changeYear: true, firstDay : EM.firstDay, yearRange:'c-100:c+15' };
	if( EM.dateFormat ) datepicker_vals.dateFormat = EM.dateFormat;
	if( EM.yearRange ) datepicker_vals.yearRange = EM.yearRange;
	jQuery(document).triggerHandler('em_datepicker', datepicker_vals);
	
	//apply datepickers
	dateDivs = wrap.find('.em-date-single, .em-date-range');
	if( dateDivs.length > 0 ){
		//apply datepickers to elements
		dateDivs.find('input.em-date-input-loc').each(function(i,dateInput){
			//init the datepicker
			var dateInput = jQuery(dateInput);
			var dateValue = dateInput.nextAll('input.em-date-input').first();
			var dateValue_value = dateValue.val();
			dateInput.datepicker(datepicker_vals);
			dateInput.datepicker('option', 'altField', dateValue);
			//now set the value
			if( dateValue_value ){
				var this_date_formatted = jQuery.datepicker.formatDate( EM.dateFormat, jQuery.datepicker.parseDate('yy-mm-dd', dateValue_value) );
				dateInput.val(this_date_formatted);
				dateValue.val(dateValue_value);
			}
			//add logic for texts
			dateInput.on('change', function(){
				if( jQuery(this).val() == '' ){
					jQuery(this).nextAll('.em-date-input').first().val('');
				}
			});
		});
		//deal with date ranges
		dateDivs.filter('.em-date-range').find('input.em-date-input-loc').each(function(i,dateInput){
			//finally, apply start/end logic to this field
			dateInput = jQuery(dateInput);
			if( dateInput.hasClass('em-date-start') ){
				dateInput.datepicker('option','onSelect', function( selectedDate ) {
					//get corresponding end date input, we expect ranges to be contained in .em-date-range with a start/end input element
					var startDate = jQuery(this);
					var endDate = startDate.parents('.em-date-range').find('.em-date-end').first();
					var startValue = startDate.nextAll('input.em-date-input').first().val();
					var endValue = endDate.nextAll('input.em-date-input').first().val();
					startDate.trigger('em_datepicker_change');
					if( startValue > endValue && endValue != '' ){
						endDate.datepicker( "setDate" , selectedDate );
						endDate.trigger('change').trigger('em_datepicker_change');
					}
					endDate.datepicker( "option", 'minDate', selectedDate );
				});
			}else if( dateInput.hasClass('em-date-end') ){
				var startInput = dateInput.parents('.em-date-range').find('.em-date-start').first();
				if( startInput.val() != '' ){
					dateInput.datepicker('option', 'minDate', startInput.val());
				}
			}
		});
	}
}
function em_setup_timepicker(wrap){
	wrap = jQuery(wrap);
	var timepicker_options = {
		step:15
	}
	timepicker_options.timeFormat = EM.show24hours == 1 ? 'G:i':'g:i A';
	jQuery(document).triggerHandler('em_timepicker_options', timepicker_options);
	wrap.find(".em-time-input").em_timepicker(timepicker_options);

	// Keep the duration between the two inputs.
	wrap.find(".em-time-range input.em-time-start").each( function(i, el){
		var time = jQuery(el);
		time.data('oldTime', time.em_timepicker('getSecondsFromMidnight'));
	}).on('change', function() {
		var start = jQuery(this);
		var end = start.nextAll('.em-time-end');
		if (end.val()) { // Only update when second input has a value.
			// Calculate duration.
			var oldTime = start.data('oldTime');
			var duration = (end.em_timepicker('getSecondsFromMidnight') - oldTime) * 1000;
			var time = start.em_timepicker('getSecondsFromMidnight');
			if( end.em_timepicker('getSecondsFromMidnight') >= oldTime ){
				// Calculate and update the time in the second input.
				end.em_timepicker('setTime', new Date(start.em_timepicker('getTime').getTime() + duration));
			}
			start.data('oldTime', time);
		}
	});
	// Validate.
	wrap.find(".event-form-when .em-time-range input.em-time-end").on('change', function() {
		var end = jQuery(this);
		var start = end.prevAll('.em-time-start');
		var wrapper = end.closest('.event-form-when');
		var start_date = wrapper.find('.em-date-end').val();
		var end_date = wrapper.find('.em-date-start').val();
		if( start.val() ){
			if( start.em_timepicker('getTime') > end.em_timepicker('getTime') && ( end_date.length == 0 || start_date == end_date ) ) { end.addClass("error"); }
			else { end.removeClass("error"); }
		}
	});
	wrap.find(".event-form-when .em-date-end").on('change', function(){
		jQuery(this).closest('.event-form-when').find('.em-time-end').trigger('change');
	});
	//Sort out all day checkbox
	wrap.find('.em-time-range input.em-time-all-day').on('change', function(){
		var allday = jQuery(this);
		if( allday.is(':checked') ){
			allday.siblings('.em-time-input').css('background-color','#ccc');
		}else{
			allday.siblings('.em-time-input').css('background-color','#fff');
		}
	}).trigger('change');
}

/* Useful function for adding the em_ajax flag to a url, regardless of querystring format */
var em_ajaxify = function(url){
	if ( url.search('em_ajax=0') != -1){
		url = url.replace('em_ajax=0','em_ajax=1');
	}else if( url.search(/\?/) != -1 ){
		url = url + "&em_ajax=1";
	}else{
		url = url + "?em_ajax=1";
	}
	return url;
};

/*
 * MAP FUNCTIONS
 */
var em_maps_loaded = false;
var maps = {};
var maps_markers = {};
var infoWindow;
//loads maps script if not already loaded and executes EM maps script
function em_maps_load(){
	if( !em_maps_loaded ){
		if ( jQuery('script#google-maps').length == 0 && ( typeof google !== 'object' || typeof google.maps !== 'object' ) ){ 
			var script = document.createElement("script");
			script.type = "text/javascript";
			script.id = "google-maps";
			var proto = (EM.is_ssl) ? 'https:' : 'http:';
			if( typeof EM.google_maps_api !== 'undefined' ){
				script.src = proto + '//maps.google.com/maps/api/js?v=quarterly&libraries=places&callback=em_maps&key='+EM.google_maps_api;
			}else{
				script.src = proto + '//maps.google.com/maps/api/js?v=quarterly&libraries=places&callback=em_maps';
			}
			document.body.appendChild(script);
		}else if( typeof google === 'object' && typeof google.maps === 'object' && !em_maps_loaded ){
			em_maps();
		}else if( jQuery('script#google-maps').length > 0 ){
			jQuery(window).load(function(){ if( !em_maps_loaded ) em_maps(); }); //google isn't loaded so wait for page to load resources
		}
	}
}
//re-usable function to load global location maps
function em_maps_load_locations(el){
	var el = jQuery(el);
	var map_id = el.attr('id').replace('em-locations-map-','');
	var em_data = jQuery.parseJSON( el.nextAll('.em-locations-map-coords').first().text() );
	if( em_data == null ){
		var em_data = jQuery.parseJSON( jQuery('#em-locations-map-coords-'+map_id).text() );
	}
	jQuery.getJSON(document.URL, em_data , function(data){
		if(data.length > 0){
			//define default options and allow option for extension via event triggers
			  var map_options = { mapTypeId: google.maps.MapTypeId.ROADMAP };
			  if( typeof EM.google_map_id_styles == 'object' && typeof EM.google_map_id_styles[map_id] !== 'undefined' ){ console.log(EM.google_map_id_styles[map_id]); map_options.styles = EM.google_map_id_styles[map_id]; }
			  else if( typeof EM.google_maps_styles !== 'undefined' ){ map_options.styles = EM.google_maps_styles; }
			  jQuery(document).triggerHandler('em_maps_locations_map_options', map_options);
			  var marker_options = {};
			  jQuery(document).triggerHandler('em_maps_location_marker_options', marker_options);
			  
			  maps[map_id] = new google.maps.Map(el[0], map_options);
			  maps_markers[map_id] = [];

			  var bounds = new google.maps.LatLngBounds();
			  
			  jQuery.map( data, function( location, i ){
				  if( !(location.location_latitude == 0 && location.location_longitude == 0) ){
					var latitude = parseFloat( location.location_latitude );
					var longitude = parseFloat( location.location_longitude );
					var location_position = new google.maps.LatLng( latitude, longitude );
					//extend the default marker options
					jQuery.extend(marker_options, {
					    position: location_position, 
					    map: maps[map_id]
					})
					var marker = new google.maps.Marker(marker_options);
					maps_markers[map_id].push(marker);
					marker.setTitle(location.location_name);
					var myContent = '<div class="em-map-balloon"><div id="em-map-balloon-'+map_id+'" class="em-map-balloon-content">'+ location.location_balloon +'</div></div>';
					em_map_infobox(marker, myContent, maps[map_id]);
					//extend bounds
					bounds.extend(new google.maps.LatLng(latitude,longitude))
				  }
			  });
			  // Zoom in to the bounds
			  maps[map_id].fitBounds(bounds);
			  
			//Call a hook if exists
			jQuery(document).triggerHandler('em_maps_locations_hook', [maps[map_id], data, map_id, maps_markers[map_id]]);
		}else{
			el.children().first().html('No locations found');
			jQuery(document).triggerHandler('em_maps_locations_hook_not_found', [el]);
		}
	});
}
function em_maps_load_location(el){
	el = jQuery(el);
	var map_id = el.attr('id').replace('em-location-map-','');
	em_LatLng = new google.maps.LatLng( jQuery('#em-location-map-coords-'+map_id+' .lat').text(), jQuery('#em-location-map-coords-'+map_id+' .lng').text());
	//extend map and markers via event triggers
	var map_options = {
	    zoom: 14,
	    center: em_LatLng,
	    mapTypeId: google.maps.MapTypeId.ROADMAP,
	    mapTypeControl: false,
	    gestureHandling: 'cooperative'
	};
	if( typeof EM.google_map_id_styles == 'object' && typeof EM.google_map_id_styles[map_id] !== 'undefined' ){ console.log(EM.google_map_id_styles[map_id]); map_options.styles = EM.google_map_id_styles[map_id]; }
	else if( typeof EM.google_maps_styles !== 'undefined' ){ map_options.styles = EM.google_maps_styles; }
	jQuery(document).triggerHandler('em_maps_location_map_options', map_options);
	maps[map_id] = new google.maps.Map( document.getElementById('em-location-map-'+map_id), map_options);
	var marker_options = {
	    position: em_LatLng,
	    map: maps[map_id]
	};
	jQuery(document).triggerHandler('em_maps_location_marker_options', marker_options);
	maps_markers[map_id] = new google.maps.Marker(marker_options);
	infoWindow = new google.maps.InfoWindow({ content: jQuery('#em-location-map-info-'+map_id+' .em-map-balloon').get(0) });
	infoWindow.open(maps[map_id],maps_markers[map_id]);
	maps[map_id].panBy(40,-70);
	
	//JS Hook for handling map after instantiation
	//Example hook, which you can add elsewhere in your theme's JS - jQuery(document).on('em_maps_location_hook', function(){ alert('hi');} );
	jQuery(document).triggerHandler('em_maps_location_hook', [maps[map_id], infoWindow, maps_markers[map_id], map_id]);
	//map resize listener
	jQuery(window).on('resize', function(e) {
		google.maps.event.trigger(maps[map_id], "resize");
		maps[map_id].setCenter(maps_markers[map_id].getPosition());
		maps[map_id].panBy(40,-70);
	});
}
jQuery(document).on('em_search_ajax', function(e, vars, wrapper){
	if( em_maps_loaded ){
		wrapper.find('.em-location-map').each( function(index, el){ em_maps_load_location(el); } );
		wrapper.find('.em-locations-map').each( function(index, el){ em_maps_load_locations(el); });
	}
});
//Load single maps (each map is treated as a seperate map).
function em_maps() {
	//Find all the maps on this page and load them
	jQuery('.em-location-map').each( function(index, el){ em_maps_load_location(el); } );	
	jQuery('.em-locations-map').each( function(index, el){ em_maps_load_locations(el); } );
	
	//Location stuff - only needed if inputs for location exist
	if( jQuery('select#location-select-id, input#location-address').length > 0 ){
		var map, marker;
		//load map info
		var refresh_map_location = function(){
			var location_latitude = jQuery('#location-latitude').val();
			var location_longitude = jQuery('#location-longitude').val();
			if( !(location_latitude == 0 && location_longitude == 0) ){
				var position = new google.maps.LatLng(location_latitude, location_longitude); //the location coords
				marker.setPosition(position);
				var mapTitle = (jQuery('input#location-name').length > 0) ? jQuery('input#location-name').val():jQuery('input#title').val();
				mapTitle = em_esc_attr(mapTitle);
				marker.setTitle( mapTitle );
				jQuery('#em-map').show();
				jQuery('#em-map-404').hide();
				google.maps.event.trigger(map, 'resize');
				map.setCenter(position);
				map.panBy(40,-55);
				infoWindow.setContent( 
					'<div id="location-balloon-content"><strong>' + mapTitle + '</strong><br>' + 
					em_esc_attr(jQuery('#location-address').val()) + 
					'<br>' + em_esc_attr(jQuery('#location-town').val()) + 
					'</div>'
				);
				infoWindow.open(map, marker);
				jQuery(document).triggerHandler('em_maps_location_hook', [map, infoWindow, marker, 0]);
			} else {
    			jQuery('#em-map').hide();
    			jQuery('#em-map-404').show();
			}
		};
		
		//Add listeners for changes to address
		var get_map_by_id = function(id){
			if(jQuery('#em-map').length > 0){
				jQuery.getJSON(document.URL,{ em_ajax_action:'get_location', id:id }, function(data){
					if( data.location_latitude!=0 && data.location_longitude!=0 ){
						loc_latlng = new google.maps.LatLng(data.location_latitude, data.location_longitude);
						marker.setPosition(loc_latlng);
						marker.setTitle( data.location_name );
						marker.setDraggable(false);
						jQuery('#em-map').show();
						jQuery('#em-map-404').hide();
						map.setCenter(loc_latlng);
						map.panBy(40,-55);
						infoWindow.setContent( '<div id="location-balloon-content">'+ data.location_balloon +'</div>');
						infoWindow.open(map, marker);
						google.maps.event.trigger(map, 'resize');
						jQuery(document).triggerHandler('em_maps_location_hook', [map, infoWindow, marker, 0]);
					}else{
						jQuery('#em-map').hide();
						jQuery('#em-map-404').show();
					}
				});
			}
		};
		jQuery('#location-select-id, input#location-id').on('change', function(){get_map_by_id(jQuery(this).val());} );
		jQuery('#location-name, #location-town, #location-address, #location-state, #location-postcode, #location-country').on('change', function(){
			//build address
			if( jQuery(this).prop('readonly') === true ) return;
			var addresses = [ jQuery('#location-address').val(), jQuery('#location-town').val(), jQuery('#location-state').val(), jQuery('#location-postcode').val() ];
			var address = '';
			jQuery.each( addresses, function(i, val){
				if( val != '' ){
					address = ( address == '' ) ? address+val:address+', '+val;
				}
			});
			if( address == '' ){ //in case only name is entered, no address
				jQuery('#em-map').hide();
				jQuery('#em-map-404').show();
				return false;
			}
			//do country last, as it's using the text version
			if( jQuery('#location-country option:selected').val() != 0 ){
				address = ( address == '' ) ? address+jQuery('#location-country option:selected').text():address+', '+jQuery('#location-country option:selected').text();
			}
			if( address != '' && jQuery('#em-map').length > 0 ){
				geocoder.geocode( { 'address': address }, function(results, status) {
				    if (status == google.maps.GeocoderStatus.OK) {
						jQuery('#location-latitude').val(results[0].geometry.location.lat());
						jQuery('#location-longitude').val(results[0].geometry.location.lng());
					}
				    refresh_map_location();
				});
			}
		});
		
		//Load map
		if(jQuery('#em-map').length > 0){
			var em_LatLng = new google.maps.LatLng(0, 0);
			var map_options = {
				    zoom: 14,
				    center: em_LatLng,
				    mapTypeId: google.maps.MapTypeId.ROADMAP,
				    mapTypeControl: false,
				    gestureHandling: 'cooperative'
			};
			if( typeof EM.google_maps_styles !== 'undefined' ){ map_options.styles = EM.google_maps_styles; }
			map = new google.maps.Map( document.getElementById('em-map'), map_options);
			var marker = new google.maps.Marker({
			    position: em_LatLng,
			    map: map,
			    draggable: true
			});
			infoWindow = new google.maps.InfoWindow({
			    content: ''
			});
			var geocoder = new google.maps.Geocoder();
			google.maps.event.addListener(infoWindow, 'domready', function() { 
				document.getElementById('location-balloon-content').parentNode.style.overflow=''; 
				document.getElementById('location-balloon-content').parentNode.parentNode.style.overflow=''; 
			});
			google.maps.event.addListener(marker, 'dragend', function() {
				var position = marker.getPosition();
				jQuery('#location-latitude').val(position.lat());
				jQuery('#location-longitude').val(position.lng());
				map.setCenter(position);
				map.panBy(40,-55);
			});
			if( jQuery('#location-select-id').length > 0 ){
				jQuery('#location-select-id').trigger('change');
			}else{
				refresh_map_location();
			}
			jQuery(document).triggerHandler('em_map_loaded', [map, infoWindow, marker]);
		}
		//map resize listener
		jQuery(window).on('resize', function(e) {
			google.maps.event.trigger(map, "resize");
			map.setCenter(marker.getPosition());
			map.panBy(40,-55);
		});
	}
	em_maps_loaded = true; //maps have been loaded
	jQuery(document).triggerHandler('em_maps_loaded');
}
  
function em_map_infobox(marker, message, map) {
  var iw = new google.maps.InfoWindow({ content: message });
  google.maps.event.addListener(marker, 'click', function() {
	if( infoWindow ) infoWindow.close();
	infoWindow = iw;
    iw.open(map,marker);
  });
}

function em_esc_attr( str ){
	if( typeof str !== 'string' ) return '';
	return str.replace(/</gi,'&lt;').replace(/>/gi,'&gt;');
}

/*!
 * jquery-timepicker v1.13.16 - Copyright (c) 2020 Jon Thornton - https://www.jonthornton.com/jquery-timepicker/
 * Did a search/replace of timepicker to em_timepicker to prevent conflicts.
 */
(function(){"use strict";function _typeof(obj){"@babel/helpers - typeof";if(typeof Symbol==="function"&&typeof Symbol.iterator==="symbol"){_typeof=function(obj){return typeof obj}}else{_typeof=function(obj){return obj&&typeof Symbol==="function"&&obj.constructor===Symbol&&obj!==Symbol.prototype?"symbol":typeof obj}}return _typeof(obj)}function _classCallCheck(instance,Constructor){if(!(instance instanceof Constructor)){throw new TypeError("Cannot call a class as a function")}}function _defineProperties(target,props){for(var i=0;i<props.length;i++){var descriptor=props[i];descriptor.enumerable=descriptor.enumerable||false;descriptor.configurable=true;if("value"in descriptor)descriptor.writable=true;Object.defineProperty(target,descriptor.key,descriptor)}}function _createClass(Constructor,protoProps,staticProps){if(protoProps)_defineProperties(Constructor.prototype,protoProps);if(staticProps)_defineProperties(Constructor,staticProps);return Constructor}function _defineProperty(obj,key,value){if(key in obj){Object.defineProperty(obj,key,{value:value,enumerable:true,configurable:true,writable:true})}else{obj[key]=value}return obj}function ownKeys(object,enumerableOnly){var keys=Object.keys(object);if(Object.getOwnPropertySymbols){var symbols=Object.getOwnPropertySymbols(object);if(enumerableOnly)symbols=symbols.filter(function(sym){return Object.getOwnPropertyDescriptor(object,sym).enumerable});keys.push.apply(keys,symbols)}return keys}function _objectSpread2(target){for(var i=1;i<arguments.length;i++){var source=arguments[i]!=null?arguments[i]:{};if(i%2){ownKeys(Object(source),true).forEach(function(key){_defineProperty(target,key,source[key])})}else if(Object.getOwnPropertyDescriptors){Object.defineProperties(target,Object.getOwnPropertyDescriptors(source))}else{ownKeys(Object(source)).forEach(function(key){Object.defineProperty(target,key,Object.getOwnPropertyDescriptor(source,key))})}}return target}function _unsupportedIterableToArray(o,minLen){if(!o)return;if(typeof o==="string")return _arrayLikeToArray(o,minLen);var n=Object.prototype.toString.call(o).slice(8,-1);if(n==="Object"&&o.constructor)n=o.constructor.name;if(n==="Map"||n==="Set")return Array.from(n);if(n==="Arguments"||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n))return _arrayLikeToArray(o,minLen)}function _arrayLikeToArray(arr,len){if(len==null||len>arr.length)len=arr.length;for(var i=0,arr2=new Array(len);i<len;i++)arr2[i]=arr[i];return arr2}function _createForOfIteratorHelper(o){if(typeof Symbol==="undefined"||o[Symbol.iterator]==null){if(Array.isArray(o)||(o=_unsupportedIterableToArray(o))){var i=0;var F=function(){};return{s:F,n:function(){if(i>=o.length)return{done:true};return{done:false,value:o[i++]}},e:function(e){throw e},f:F}}throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}var it,normalCompletion=true,didErr=false,err;return{s:function(){it=o[Symbol.iterator]()},n:function(){var step=it.next();normalCompletion=step.done;return step},e:function(e){didErr=true;err=e},f:function(){try{if(!normalCompletion&&it.return!=null)it.return()}finally{if(didErr)throw err}}}}var ONE_DAY=86400;var roundingFunction=function roundingFunction(seconds,settings){if(seconds===null){return null}else if(typeof settings.step!=="number"){return seconds}else{var offset=seconds%(settings.step*60);var start=settings.minTime||0;offset-=start%(settings.step*60);if(offset>=settings.step*30){seconds+=settings.step*60-offset}else{seconds-=offset}return _moduloSeconds(seconds,settings)}};function _moduloSeconds(seconds,settings){if(seconds==ONE_DAY&&settings.show2400){return seconds}return seconds%ONE_DAY}var DEFAULT_SETTINGS={appendTo:"body",className:null,closeOnWindowScroll:false,disableTextInput:false,disableTimeRanges:[],disableTouchKeyboard:false,durationTime:null,forceRoundTime:false,lang:{},listWidth:null,maxTime:null,minTime:null,noneOption:false,orientation:"l",roundingFunction:roundingFunction,scrollDefault:null,selectOnBlur:false,show2400:false,showDuration:false,showOn:["click","focus"],showOnFocus:true,step:30,stopScrollPropagation:false,timeFormat:"g:ia",typeaheadHighlight:true,useSelect:false,wrapHours:true};var DEFAULT_LANG={am:"am",pm:"pm",AM:"AM",PM:"PM",decimal:".",mins:"mins",hr:"hr",hrs:"hrs"};var Timepicker=function(){function Timepicker(targetEl){var options=arguments.length>1&&arguments[1]!==undefined?arguments[1]:{};_classCallCheck(this,Timepicker);this._handleFormatValue=this._handleFormatValue.bind(this);this._handleKeyUp=this._handleKeyUp.bind(this);this.targetEl=targetEl;var attrOptions=Timepicker.extractAttrOptions(targetEl,Object.keys(DEFAULT_SETTINGS));this.settings=this.parseSettings(_objectSpread2(_objectSpread2(_objectSpread2({},DEFAULT_SETTINGS),options),attrOptions))}_createClass(Timepicker,[{key:"hideMe",value:function hideMe(){if(this.settings.useSelect){this.targetEl.blur();return}if(!this.list||!Timepicker.isVisible(this.list)){return}if(this.settings.selectOnBlur){this._selectValue()}this.list.hide();var hideTimepickerEvent=new CustomEvent("hideTimepicker");this.targetEl.dispatchEvent(hideTimepickerEvent)}},{key:"_findRow",value:function _findRow(value){if(!value&&value!==0){return false}var out=false;var value=this.settings.roundingFunction(value,this.settings);if(!this.list){return false}this.list.find("li").each(function(i,obj){var parsed=Number.parseInt(obj.dataset.time);if(Number.isNaN(parsed)){return}if(parsed==value){out=obj;return false}});return out}},{key:"_hideKeyboard",value:function _hideKeyboard(){return(window.navigator.msMaxTouchPoints||"ontouchstart"in document)&&this.settings.disableTouchKeyboard}},{key:"_setTimeValue",value:function _setTimeValue(value,source){if(this.targetEl.nodeName==="INPUT"){if(value!==null||this.targetEl.value!=""){this.targetEl.value=value}var tp=this;var settings=tp.settings;if(settings.useSelect&&source!="select"&&tp.list){tp.list.val(tp._roundAndFormatTime(tp.time2int(value)))}}var selectTimeEvent=new Event("selectTime");if(this.selectedValue!=value){this.selectedValue=value;var changeTimeEvent=new Event("changeTime");var changeEvent=new CustomEvent("change",{detail:"em_timepicker"});if(source=="select"){this.targetEl.dispatchEvent(selectTimeEvent);this.targetEl.dispatchEvent(changeTimeEvent);this.targetEl.dispatchEvent(changeEvent)}else if(["error","initial"].indexOf(source)==-1){this.targetEl.dispatchEvent(changeTimeEvent)}return true}else{if(["error","initial"].indexOf(source)==-1){this.targetEl.dispatchEvent(selectTimeEvent)}return false}}},{key:"_getTimeValue",value:function _getTimeValue(){if(this.targetEl.nodeName==="INPUT"){return this.targetEl.value}else{return this.selectedValue}}},{key:"_selectValue",value:function _selectValue(){var tp=this;var settings=tp.settings;var list=tp.list;var cursor=list.find(".ui-em_timepicker-selected");if(cursor.hasClass("ui-em_timepicker-disabled")){return false}if(!cursor.length){return true}var timeValue=cursor.get(0).dataset.time;if(timeValue){var parsedTimeValue=Number.parseInt(timeValue);if(!Number.isNaN(parsedTimeValue)){timeValue=parsedTimeValue}}if(timeValue!==null){if(typeof timeValue!="string"){timeValue=tp._int2time(timeValue)}tp._setTimeValue(timeValue,"select")}return true}},{key:"time2int",value:function time2int(timeString){if(timeString===""||timeString===null||timeString===undefined)return null;if(timeString instanceof Date){return timeString.getHours()*3600+timeString.getMinutes()*60+timeString.getSeconds()}if(typeof timeString!="string"){return timeString}timeString=timeString.toLowerCase().replace(/[\s\.]/g,"");if(timeString.slice(-1)=="a"||timeString.slice(-1)=="p"){timeString+="m"}var pattern=/^(([^0-9]*))?([0-9]?[0-9])(([0-5][0-9]))?(([0-5][0-9]))?(([^0-9]*))$/;var hasDelimetersMatch=timeString.match(/\W/);if(hasDelimetersMatch){pattern=/^(([^0-9]*))?([0-9]?[0-9])(\W+([0-5][0-9]?))?(\W+([0-5][0-9]))?(([^0-9]*))$/}var time=timeString.match(pattern);if(!time){return null}var hour=parseInt(time[3]*1,10);var ampm=time[2]||time[9];var hours=hour;var minutes=time[5]*1||0;var seconds=time[7]*1||0;if(!ampm&&time[3].length==2&&time[3][0]=="0"){ampm="am"}if(hour<=12&&ampm){ampm=ampm.trim();var isPm=ampm==this.settings.lang.pm||ampm==this.settings.lang.PM;if(hour==12){hours=isPm?12:0}else{hours=hour+(isPm?12:0)}}else{var t=hour*3600+minutes*60+seconds;if(t>=ONE_DAY+(this.settings.show2400?1:0)){if(this.settings.wrapHours===false){return null}hours=hour%24}}var timeInt=hours*3600+minutes*60+seconds;if(hour<12&&!ampm&&this.settings._twelveHourTime&&this.settings.scrollDefault){var delta=timeInt-this.settings.scrollDefault();if(delta<0&&delta>=ONE_DAY/-2){timeInt=(timeInt+ONE_DAY/2)%ONE_DAY}}return timeInt}},{key:"parseSettings",value:function parseSettings(settings){var _this=this;settings.lang=_objectSpread2(_objectSpread2({},DEFAULT_LANG),settings.lang);this.settings=settings;if(settings.minTime){settings.minTime=this.time2int(settings.minTime)}if(settings.maxTime){settings.maxTime=this.time2int(settings.maxTime)}if(settings.listWidth){settings.listWidth=this.time2int(settings.listWidth)}if(settings.durationTime&&typeof settings.durationTime!=="function"){settings.durationTime=this.time2int(settings.durationTime)}if(settings.scrollDefault=="now"){settings.scrollDefault=function(){return settings.roundingFunction(_this.time2int(new Date),settings)}}else if(settings.scrollDefault&&typeof settings.scrollDefault!="function"){var val=settings.scrollDefault;settings.scrollDefault=function(){return settings.roundingFunction(_this.time2int(val),settings)}}else if(settings.minTime){settings.scrollDefault=function(){return settings.roundingFunction(settings.minTime,settings)}}if(typeof settings.timeFormat==="string"&&settings.timeFormat.match(/[gh]/)){settings._twelveHourTime=true}if(settings.showOnFocus===false&&settings.showOn.indexOf("focus")!=-1){settings.showOn.splice(settings.showOn.indexOf("focus"),1)}if(!settings.disableTimeRanges){settings.disableTimeRanges=[]}if(settings.disableTimeRanges.length>0){for(var i in settings.disableTimeRanges){settings.disableTimeRanges[i]=[this.time2int(settings.disableTimeRanges[i][0]),this.time2int(settings.disableTimeRanges[i][1])]}settings.disableTimeRanges=settings.disableTimeRanges.sort(function(a,b){return a[0]-b[0]});for(var i=settings.disableTimeRanges.length-1;i>0;i--){if(settings.disableTimeRanges[i][0]<=settings.disableTimeRanges[i-1][1]){settings.disableTimeRanges[i-1]=[Math.min(settings.disableTimeRanges[i][0],settings.disableTimeRanges[i-1][0]),Math.max(settings.disableTimeRanges[i][1],settings.disableTimeRanges[i-1][1])];settings.disableTimeRanges.splice(i,1)}}}return settings}},{key:"_disableTextInputHandler",value:function _disableTextInputHandler(e){switch(e.keyCode){case 13:case 9:return;default:e.preventDefault()}}},{key:"_int2duration",value:function _int2duration(seconds,step){seconds=Math.abs(seconds);var minutes=Math.round(seconds/60),duration=[],hours,mins;if(minutes<60){duration=[minutes,this.settings.lang.mins]}else{hours=Math.floor(minutes/60);mins=minutes%60;if(step==30&&mins==30){hours+=this.settings.lang.decimal+5}duration.push(hours);duration.push(hours==1?this.settings.lang.hr:this.settings.lang.hrs);if(step!=30&&mins){duration.push(mins);duration.push(this.settings.lang.mins)}}return duration.join(" ")}},{key:"_roundAndFormatTime",value:function _roundAndFormatTime(seconds){seconds=this.settings.roundingFunction(seconds,this.settings);if(seconds!==null){return this._int2time(seconds)}}},{key:"_int2time",value:function _int2time(timeInt){if(typeof timeInt!="number"){return null}var seconds=parseInt(timeInt%60),minutes=parseInt(timeInt/60%60),hours=parseInt(timeInt/(60*60)%24);var time=new Date(1970,0,2,hours,minutes,seconds,0);if(isNaN(time.getTime())){return null}if(typeof this.settings.timeFormat==="function"){return this.settings.timeFormat(time)}var output="";var hour,code;for(var i=0;i<this.settings.timeFormat.length;i++){code=this.settings.timeFormat.charAt(i);switch(code){case"a":output+=time.getHours()>11?this.settings.lang.pm:this.settings.lang.am;break;case"A":output+=time.getHours()>11?this.settings.lang.PM:this.settings.lang.AM;break;case"g":hour=time.getHours()%12;output+=hour===0?"12":hour;break;case"G":hour=time.getHours();if(timeInt===ONE_DAY)hour=this.settings.show2400?24:0;output+=hour;break;case"h":hour=time.getHours()%12;if(hour!==0&&hour<10){hour="0"+hour}output+=hour===0?"12":hour;break;case"H":hour=time.getHours();if(timeInt===ONE_DAY)hour=this.settings.show2400?24:0;output+=hour>9?hour:"0"+hour;break;case"i":var minutes=time.getMinutes();output+=minutes>9?minutes:"0"+minutes;break;case"s":seconds=time.getSeconds();output+=seconds>9?seconds:"0"+seconds;break;case"\\":i++;output+=this.settings.timeFormat.charAt(i);break;default:output+=code}}return output}},{key:"_setSelected",value:function _setSelected(){var list=this.list;list.find("li").removeClass("ui-em_timepicker-selected");var timeValue=this.time2int(this._getTimeValue());if(timeValue===null){return}var selected=this._findRow(timeValue);if(selected){var selectedRect=selected.getBoundingClientRect();var listRect=list.get(0).getBoundingClientRect();var topDelta=selectedRect.top-listRect.top;if(topDelta+selectedRect.height>listRect.height||topDelta<0){var newScroll=list.scrollTop()+(selectedRect.top-listRect.top)-selectedRect.height;list.scrollTop(newScroll)}var parsed=Number.parseInt(selected.dataset.time);if(this.settings.forceRoundTime||parsed===timeValue){selected.classList.add("ui-em_timepicker-selected")}}}},{key:"_isFocused",value:function _isFocused(el){return el===document.activeElement}},{key:"_handleFormatValue",value:function _handleFormatValue(e){if(e&&e.detail=="em_timepicker"){return}this._formatValue(e)}},{key:"_formatValue",value:function _formatValue(e,origin){if(this.targetEl.value===""){this._setTimeValue(null,origin);return}if(this._isFocused(this.targetEl)&&(!e||e.type!="change")){return}var settings=this.settings;var seconds=this.time2int(this.targetEl.value);if(seconds===null){var timeFormatErrorEvent=new CustomEvent("timeFormatError");this.targetEl.dispatchEvent(timeFormatErrorEvent);return}var rangeError=false;if(settings.minTime!==null&&settings.maxTime!==null&&(seconds<settings.minTime||seconds>settings.maxTime)){rangeError=true}var _iterator=_createForOfIteratorHelper(settings.disableTimeRanges),_step;try{for(_iterator.s();!(_step=_iterator.n()).done;){var range=_step.value;if(seconds>=range[0]&&seconds<range[1]){rangeError=true;break}}}catch(err){_iterator.e(err)}finally{_iterator.f()}if(settings.forceRoundTime){var roundSeconds=settings.roundingFunction(seconds,settings);if(roundSeconds!=seconds){seconds=roundSeconds;origin=null}}var prettyTime=this._int2time(seconds);if(rangeError){this._setTimeValue(prettyTime);var timeRangeErrorEvent=new CustomEvent("timeRangeError");this.targetEl.dispatchEvent(timeRangeErrorEvent)}else{this._setTimeValue(prettyTime,origin)}}},{key:"_generateNoneElement",value:function _generateNoneElement(optionValue,useSelect){var label,className,value;if(_typeof(optionValue)=="object"){label=optionValue.label;className=optionValue.className;value=optionValue.value}else if(typeof optionValue=="string"){label=optionValue;value=""}else{$.error("Invalid noneOption value")}var el;if(useSelect){el=document.createElement("option");el.value=value}else{el=document.createElement("li");el.dataset.time=String(value)}el.innerText=label;el.classList.add(className);return el}},{key:"_handleKeyUp",value:function _handleKeyUp(e){if(!this.list||!Timepicker.isVisible(this.list)||this.settings.disableTextInput){return true}if(e.type==="paste"||e.type==="cut"){setTimeout(function(){if(this.settings.typeaheadHighlight){this._setSelected()}else{this.list.hide()}},0);return}switch(e.keyCode){case 96:case 97:case 98:case 99:case 100:case 101:case 102:case 103:case 104:case 105:case 48:case 49:case 50:case 51:case 52:case 53:case 54:case 55:case 56:case 57:case 65:case 77:case 80:case 186:case 8:case 46:if(this.settings.typeaheadHighlight){this._setSelected()}else{this.list.hide()}break}}}],[{key:"extractAttrOptions",value:function extractAttrOptions(element,keys){var output={};var _iterator2=_createForOfIteratorHelper(keys),_step2;try{for(_iterator2.s();!(_step2=_iterator2.n()).done;){var key=_step2.value;if(key in element.dataset){output[key]=element.dataset[key]}}}catch(err){_iterator2.e(err)}finally{_iterator2.f()}return output}},{key:"isVisible",value:function isVisible(elem){var el=elem[0];return el.offsetWidth>0&&el.offsetHeight>0}},{key:"hideAll",value:function hideAll(){var _iterator3=_createForOfIteratorHelper(document.getElementsByClassName("ui-em_timepicker-input")),_step3;try{for(_iterator3.s();!(_step3=_iterator3.n()).done;){var el=_step3.value;var tp=el.em_timepickerObj;if(tp){tp.hideMe()}}}catch(err){_iterator3.e(err)}finally{_iterator3.f()}}}]);return Timepicker}();(function(factory){if((typeof exports==="undefined"?"undefined":_typeof(exports))==="object"&&exports&&(typeof module==="undefined"?"undefined":_typeof(module))==="object"&&module&&module.exports===exports){factory(require("jquery"))}else if(typeof define==="function"&&define.amd){define(["jquery"],factory)}else{factory(jQuery)}})(function($){var _lang={};var methods={init:function init(options){return this.each(function(){var self=$(this);var tp=new Timepicker(this,options);var settings=tp.settings;_lang=settings.lang;this.em_timepickerObj=tp;self.addClass("ui-em_timepicker-input");if(settings.useSelect){_render(self)}else{self.prop("autocomplete","off");if(settings.showOn){for(var i in settings.showOn){self.on(settings.showOn[i]+".em_timepicker",methods.show)}}self.on("change.em_timepicker",tp._handleFormatValue);self.on("keydown.em_timepicker",_keydownhandler);self.on("keyup.em_timepicker",tp._handleKeyUp);if(settings.disableTextInput){self.on("keydown.em_timepicker",tp._disableTextInputHandler)}self.on("cut.em_timepicker",tp._handleKeyUp);self.on("paste.em_timepicker",tp._handleKeyUp);tp._formatValue(null,"initial")}})},show:function show(e){var self=$(this);var tp=self[0].em_timepickerObj;var settings=tp.settings;if(e){e.preventDefault()}if(settings.useSelect){tp.list.trigger("focus");return}if(tp._hideKeyboard()){self.trigger("blur")}var list=tp.list;if(self.prop("readonly")){return}if(!list||list.length===0||typeof settings.durationTime==="function"){_render(self);list=tp.list}if(Timepicker.isVisible(list)){return}if(self.is("input")){tp.selectedValue=self.val()}tp._setSelected();Timepicker.hideAll();if(typeof settings.listWidth=="number"){list.width(self.outerWidth()*settings.listWidth)}list.show();var listOffset={};if(settings.orientation.match(/r/)){listOffset.left=self.offset().left+self.outerWidth()-list.outerWidth()+parseInt(list.css("marginLeft").replace("px",""),10)}else if(settings.orientation.match(/l/)){listOffset.left=self.offset().left+parseInt(list.css("marginLeft").replace("px",""),10)}else if(settings.orientation.match(/c/)){listOffset.left=self.offset().left+(self.outerWidth()-list.outerWidth())/2+parseInt(list.css("marginLeft").replace("px",""),10)}var verticalOrientation;if(settings.orientation.match(/t/)){verticalOrientation="t"}else if(settings.orientation.match(/b/)){verticalOrientation="b"}else if(self.offset().top+self.outerHeight(true)+list.outerHeight()>$(window).height()+$(window).scrollTop()){verticalOrientation="t"}else{verticalOrientation="b"}if(verticalOrientation=="t"){list.addClass("ui-em_timepicker-positioned-top");listOffset.top=self.offset().top-list.outerHeight()+parseInt(list.css("marginTop").replace("px",""),10)}else{list.removeClass("ui-em_timepicker-positioned-top");listOffset.top=self.offset().top+self.outerHeight()+parseInt(list.css("marginTop").replace("px",""),10)}list.offset(listOffset);var selected=list.find(".ui-em_timepicker-selected");if(!selected.length){var timeInt=tp.time2int(tp._getTimeValue());if(timeInt!==null){selected=$(tp._findRow(timeInt))}else if(settings.scrollDefault){selected=$(tp._findRow(settings.scrollDefault()))}}if(!selected.length||selected.hasClass("ui-em_timepicker-disabled")){selected=list.find("li:not(.ui-em_timepicker-disabled):first")}if(selected&&selected.length){var topOffset=list.scrollTop()+selected.position().top-selected.outerHeight();list.scrollTop(topOffset)}else{list.scrollTop(0)}if(settings.stopScrollPropagation){$(document).on("wheel.ui-em_timepicker",".ui-em_timepicker-wrapper",function(e){e.preventDefault();var currentScroll=$(this).scrollTop();$(this).scrollTop(currentScroll+e.originalEvent.deltaY)})}$(document).on("mousedown.ui-em_timepicker",_closeHandler);$(window).on("resize.ui-em_timepicker",_closeHandler);if(settings.closeOnWindowScroll){$(document).on("scroll.ui-em_timepicker",_closeHandler)}self.trigger("showTimepicker");return this},hide:function hide(e){var tp=this[0].em_timepickerObj;if(tp){tp.hideMe()}Timepicker.hideAll();return this},option:function option(key,value){if(typeof key=="string"&&typeof value=="undefined"){var tp=this[0].em_timepickerObj;return tp.settings[key]}return this.each(function(){var self=$(this);var tp=self[0].em_timepickerObj;var settings=tp.settings;var list=tp.list;if(_typeof(key)=="object"){settings=$.extend(settings,key)}else if(typeof key=="string"){settings[key]=value}settings=tp.parseSettings(settings);tp.settings=settings;tp._formatValue({type:"change"},"initial");if(list){list.remove();tp.list=null}if(settings.useSelect){_render(self)}})},getSecondsFromMidnight:function getSecondsFromMidnight(){var tp=this[0].em_timepickerObj;return tp.time2int(tp._getTimeValue())},getTime:function getTime(relative_date){var tp=this[0].em_timepickerObj;var time_string=tp._getTimeValue();if(!time_string){return null}var offset=tp.time2int(time_string);if(offset===null){return null}if(!relative_date){relative_date=new Date}var time=new Date(relative_date);time.setHours(offset/3600);time.setMinutes(offset%3600/60);time.setSeconds(offset%60);time.setMilliseconds(0);return time},isVisible:function isVisible(){var tp=this[0].em_timepickerObj;return!!(tp&&tp.list&&Timepicker.isVisible(tp.list))},setTime:function setTime(value){var tp=this[0].em_timepickerObj;var settings=tp.settings;if(settings.forceRoundTime){var prettyTime=tp._roundAndFormatTime(tp.time2int(value))}else{var prettyTime=tp._int2time(tp.time2int(value))}if(value&&prettyTime===null&&settings.noneOption){prettyTime=value}tp._setTimeValue(prettyTime,"initial");tp._formatValue({type:"change"},"initial");if(tp&&tp.list){tp._setSelected()}return this},remove:function remove(){var self=this;if(!self.hasClass("ui-em_timepicker-input")){return}var tp=self[0].em_timepickerObj;var settings=tp.settings;self.removeAttr("autocomplete","off");self.removeClass("ui-em_timepicker-input");self.removeData("em_timepicker-obj");self.off(".em_timepicker");if(tp.list){tp.list.remove()}if(settings.useSelect){self.show()}tp.list=null;return this}};function _render(self){var tp=self[0].em_timepickerObj;var list=tp.list;var settings=tp.settings;if(list&&list.length){list.remove();tp.list=null}if(settings.useSelect){list=$("<select></select>",{class:"ui-em_timepicker-select"});if(self.attr("name")){list.attr("name","ui-em_timepicker-"+self.attr("name"))}var wrapped_list=list}else{list=$("<ul></ul>",{class:"ui-em_timepicker-list"});var wrapped_list=$("<div></div>",{class:"ui-em_timepicker-wrapper",tabindex:-1});wrapped_list.css({display:"none",position:"absolute"}).append(list)}if(settings.noneOption){if(settings.noneOption===true){settings.noneOption=settings.useSelect?"Time...":"None"}if($.isArray(settings.noneOption)){for(var i in settings.noneOption){if(parseInt(i,10)==i){var noneElement=tp._generateNoneElement(settings.noneOption[i],settings.useSelect);list.append(noneElement)}}}else{var noneElement=tp._generateNoneElement(settings.noneOption,settings.useSelect);list.append(noneElement)}}if(settings.className){wrapped_list.addClass(settings.className)}if((settings.minTime!==null||settings.durationTime!==null)&&settings.showDuration){var stepval=typeof settings.step=="function"?"function":settings.step;wrapped_list.addClass("ui-em_timepicker-with-duration");wrapped_list.addClass("ui-em_timepicker-step-"+settings.step)}var durStart=settings.minTime;if(typeof settings.durationTime==="function"){durStart=tp.time2int(settings.durationTime())}else if(settings.durationTime!==null){durStart=settings.durationTime}var start=settings.minTime!==null?settings.minTime:0;var end=settings.maxTime!==null?settings.maxTime:start+ONE_DAY-1;if(end<start){end+=ONE_DAY}if(end===ONE_DAY-1&&$.type(settings.timeFormat)==="string"&&settings.show2400){end=ONE_DAY}var dr=settings.disableTimeRanges;var drCur=0;var drLen=dr.length;var stepFunc=settings.step;if(typeof stepFunc!="function"){stepFunc=function stepFunc(){return settings.step}}for(var i=start,j=0;i<=end;j++,i+=stepFunc(j)*60){var timeInt=i;var timeString=tp._int2time(timeInt);if(settings.useSelect){var row=$("<option></option>",{value:timeString});row.text(timeString)}else{var row=$("<li></li>");row.addClass(timeInt%ONE_DAY<ONE_DAY/2?"ui-em_timepicker-am":"ui-em_timepicker-pm");row.attr("data-time",roundingFunction(timeInt,settings));row.text(timeString)}if((settings.minTime!==null||settings.durationTime!==null)&&settings.showDuration){var durationString=tp._int2duration(i-durStart,settings.step);if(settings.useSelect){row.text(row.text()+" ("+durationString+")")}else{var duration=$("<span></span>",{class:"ui-em_timepicker-duration"});duration.text(" ("+durationString+")");row.append(duration)}}if(drCur<drLen){if(timeInt>=dr[drCur][1]){drCur+=1}if(dr[drCur]&&timeInt>=dr[drCur][0]&&timeInt<dr[drCur][1]){if(settings.useSelect){row.prop("disabled",true)}else{row.addClass("ui-em_timepicker-disabled")}}}list.append(row)}wrapped_list.data("em_timepicker-input",self);tp.list=wrapped_list;if(settings.useSelect){if(self.val()){list.val(tp._roundAndFormatTime(tp.time2int(self.val())))}list.on("focus",function(){$(this).data("em_timepicker-input").trigger("showTimepicker")});list.on("blur",function(){$(this).data("em_timepicker-input").trigger("hideTimepicker")});list.on("change",function(){tp._setTimeValue($(this).val(),"select")});tp._setTimeValue(list.val(),"initial");self.hide().after(list)}else{var appendTo=settings.appendTo;if(typeof appendTo==="string"){appendTo=$(appendTo)}else if(typeof appendTo==="function"){appendTo=appendTo(self)}appendTo.append(wrapped_list);tp._setSelected();list.on("mousedown click","li",function(e){self.off("focus.em_timepicker");self.on("focus.em_timepicker-ie-hack",function(){self.off("focus.em_timepicker-ie-hack");self.on("focus.em_timepicker",methods.show)});if(!tp._hideKeyboard()){self[0].focus()}list.find("li").removeClass("ui-em_timepicker-selected");$(this).addClass("ui-em_timepicker-selected");if(tp._selectValue()){self.trigger("hideTimepicker");list.on("mouseup.em_timepicker click.em_timepicker","li",function(e){list.off("mouseup.em_timepicker click.em_timepicker");wrapped_list.hide()})}})}}function _closeHandler(e){if(e.target==window){return}var target=$(e.target);if(target.closest(".ui-em_timepicker-input").length||target.closest(".ui-em_timepicker-wrapper").length){return}Timepicker.hideAll();$(document).off(".ui-em_timepicker");$(window).off(".ui-em_timepicker")}function _keydownhandler(e){var self=$(this);var tp=self[0].em_timepickerObj;var list=tp.list;if(!list||!Timepicker.isVisible(list)){if(e.keyCode==40){methods.show.call(self.get(0));list=tp.list;if(!tp._hideKeyboard()){self.trigger("focus")}}else{return true}}switch(e.keyCode){case 13:if(tp._selectValue()){tp._formatValue({type:"change"});tp.hideMe()}e.preventDefault();return false;case 38:var selected=list.find(".ui-em_timepicker-selected");if(!selected.length){list.find("li").each(function(i,obj){if($(obj).position().top>0){selected=$(obj);return false}});selected.addClass("ui-em_timepicker-selected")}else if(!selected.is(":first-child")){selected.removeClass("ui-em_timepicker-selected");selected.prev().addClass("ui-em_timepicker-selected");if(selected.prev().position().top<selected.outerHeight()){list.scrollTop(list.scrollTop()-selected.outerHeight())}}return false;case 40:selected=list.find(".ui-em_timepicker-selected");if(selected.length===0){list.find("li").each(function(i,obj){if($(obj).position().top>0){selected=$(obj);return false}});selected.addClass("ui-em_timepicker-selected")}else if(!selected.is(":last-child")){selected.removeClass("ui-em_timepicker-selected");selected.next().addClass("ui-em_timepicker-selected");if(selected.next().position().top+2*selected.outerHeight()>list.outerHeight()){list.scrollTop(list.scrollTop()+selected.outerHeight())}}return false;case 27:list.find("li").removeClass("ui-em_timepicker-selected");tp.hideMe();break;case 9:tp.hideMe();break;default:return true}}$.fn.em_timepicker=function(method){if(!this.length)return this;if(methods[method]){if(!this.hasClass("ui-em_timepicker-input")){return this}return methods[method].apply(this,Array.prototype.slice.call(arguments,1))}else if(_typeof(method)==="object"||!method){return methods.init.apply(this,arguments)}else{$.error("Method "+method+" does not exist on jQuery.em_timepicker")}};$.fn.em_timepicker.defaults=DEFAULT_SETTINGS})})();