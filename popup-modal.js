(function($) {
	var popup_modal;
	function popupModal() {
		var debugPopupModal = false; // For testing set to 'true'
		if (debugPopupModal) console.log(popup_modal_data);
		if ( typeof popup_modal_data !== 'undefined' && $.isArray( popup_modal_data ) ) { // Popup Modal array exists?
			if (debugPopupModal) console.log('Looping thru popups on path: ' + window.location.pathname + ' (' + window.location.pathname.length + ')');
			for ( var i=0; i<popup_modal_data.length; i++) { // Loop through all the popups
				if (debugPopupModal) console.log('ID:' + popup_modal_data[i].id + ' - ' + popup_modal_data[i].poptitle + ' - freq:' + popup_modal_data[i].freq + ' - pages:' + popup_modal_data[i].pages + ' - onclick:' + popup_modal_data[i].click + ' - width:' + popup_modal_data[i].width + 'px - title:' + popup_modal_data[i].title + ' - color:' + popup_modal_data[i].color + ' - bg:' + popup_modal_data[i].bgcolor + ' - location:' + popup_modal_data[i].location + ' - buttons:' + popup_modal_data[i].buttons );
				if ( popup_modal_data[i].pages=='all' || (popup_modal_data[i].pages=='front' && window.location.pathname.length<2) || (popup_modal_data[i].pages=='interior' && window.location.pathname.length>1) ) { // Should the popup occur on this page?
					if (debugPopupModal) console.log('Popup ' + popup_modal_data[i].id + ' could appear on this page');
					var cookieName = 'popup-modal-' + popup_modal_data[i].id; // Get the cookie for this particular popup
					if (!getCookie(cookieName) && popup_modal_data[i].viewed!=true) { // Is it time to display this popup?
						if (debugPopupModal) console.log('View popup ' + popup_modal_data[i].id);
						popup_modal_data[i].viewed = true; // Flag the popup as being viewed during this pageview
						$('#popupModal').addClass('popup-id-' + popup_modal_data[i].id + ' popup-slug-' + popup_modal_data[i].slug );
						if (popup_modal_data[i].buttons=='left') $('#popupModal').addClass('modal-buttons-left');
						else if (popup_modal_data[i].buttons=='center') $('#popupModal').addClass('modal-buttons-center');
						else if (popup_modal_data[i].buttons=='right') $('#popupModal').addClass('modal-buttons-right');
						$('#popupModal .modal-dialog').removeClass('allow-scrolling top-center bottom-center');
						if (popup_modal_data[i].location=='top') $('#popupModal .modal-dialog').addClass('top-center');
						else if (popup_modal_data[i].location=='bottom') $('#popupModal .modal-dialog').addClass('bottom-center');
						$('#popupModal .modal-print-button').css('display',(popup_modal_data[i].click=='print'?'inline-block':'none'));
						$('#popupModal .modal-body').css('cursor',(popup_modal_data[i].click=='print'&&(popup_modal_data[i].buttons=='no'||popup_modal_data[i].buttons==null)?'pointer':'default'));
						$('#popupModal .modal-dialog').css('width',popup_modal_data[i].width);
						$('#popupModal .modal-body').css('color',popup_modal_data[i].color);
						$('#popupModal .modal-body').css('background-color',popup_modal_data[i].bgcolor);
						$('#popupModal .modal-copy').html(popup_modal_data[i].body); // Set the popup content
						$('#popupModal .modal-title').html(popup_modal_data[i].poptitle); // Set the popup title
						$('#popupModal .modal-title').css('display',(popup_modal_data[i].title=='no'?'none':'block'));
						$('html').addClass('popup-modal-open');
						setTimeout(function() {
							$('#popupModal').fadeIn(); // Display the popup
							popupModalResizer(); // Should there be scrollbars on startup?
						}, 250 );
						if (popup_modal_data[i].freq!=-1) setCookie(cookieName, popup_modal_data[i].freq); // Number of hours till this popup appears again
						popup_modal = popup_modal_data[i];
						function popupCleanup() {
							$('#popupModal').removeAttr('class');
							$('html').removeClass('popup-modal-open');
							if (debugPopupModal) console.log('--------------');
							if (typeof popup_modal_admin === 'undefined') popupModal(); // Check to see if there's another popup to show during this pageview
						}
						function popupClick(event) {
							// Must be an anchor link in the popup, close the popup and follow the link
							if (popup_modal.id != -1 && (typeof event.target.href !== 'undefined' || typeof event.target.parentElement.href !== 'undefined')) {
								$('#popupModal').fadeOut(100, function() { // Hide the popup
									popupCleanup();
								});
								return false;
							}
							event.preventDefault();
							if (popup_modal.click == 'print' && !$(event.target).hasClass('modal-close') && !$(event.target).hasClass('modal-close-button') && ($(event.target).hasClass('modal-print-button') || $(event.target).css('cursor')=='pointer')) {
								if (debugPopupModal) console.log('Print popup ' + popup_modal.id);
								window.print(); // Bring up the print console
							} else if ($(event.target).hasClass('modal-close') || $(event.target).hasClass('modal-close-button') || $(event.target).is('#popupModal') || event.keyCode==27) { 
								if (debugPopupModal) console.log('Close popup ' + popup_modal.id);
								$('#popupModal').unbind('click'); // Unblind the click hook
								$(document).unbind('keyup'); // Unblind the click hook
								$(window).off('resize', popupModalResizer); // Unbind the window resize
								if (popup_modal.id==-1) {
									$('#popupModal .modal-copy').empty();
									$('#wpwrap').css('visibility', 'visible');
									$(window).trigger('resize');
								}
								$('#popupModal').fadeOut(popup_modal.id==-1?0:400, function() { // Hide the popup
									popupCleanup();
								});
							}
						}
						$('#popupModal').click(function(event) { // Wait for click
							popupClick(event);
						});
						$(document).keyup(function(event) { // Or escape key
							if (debugPopupModal) console.log(event.keyCode);
							if(event.keyCode==27) popupClick(event);
						});
						$(window).resize(popupModalResizer); // Trap window resize
						if (debugPopupModal) console.log('Waiting for click...');
						break; // Only show one popup at a time
					} else if (debugPopupModal) console.log('Skip popup ' + popup_modal_data[i].id + (getCookie(cookieName)!=false?' - Cookie exists':'') + (popup_modal_data[i].viewed?' - Viewed this pageview':''));
				}
			}
			if (debugPopupModal && i==popup_modal_data.length) console.log('Finished with popups');
		}
	}
	function popupModalResizer() {
		// If popup is taller than window, allow scrollbars
		if ($('#popupModal .modal-content').outerHeight()>=window.innerHeight/1.05) {
			$('#popupModal .modal-dialog').addClass('allow-scrolling');
			$('#popupModal .modal-close').css('color',popup_modal.color);
		} else {
			$('#popupModal .modal-dialog').removeClass('allow-scrolling');
			$('#popupModal .modal-close').removeAttr('style');
		}
	}
	if (typeof popup_modal_admin === 'undefined') popupModal();

	/* Admin JavaScript */
	if (typeof popup_modal_admin !== 'undefined') {
		if ($('#popup_disable').prop('checked')) {
			$('.metabox-prefs .metabox-options').css('opacity','0.4');
			$('.metabox-prefs .metabox-options input, .metabox-prefs .metabox-options select').prop( "disabled", true );
			$('.metabox-prefs .wp-picker-container').css('display','none');
			$('.popup-modal-test').prop( "disabled", true );
		}
		$(document).ready(function() {
			$('.popup-modal-test').click( function(event) {
				event.preventDefault();
				popup_modal_data[0].viewed = false;
				popup_modal_data[0].click		= $('#popup_click').val();
				popup_modal_data[0].location	= $('#popup_location').val();
				popup_modal_data[0].width		= $('#popup_width').val();
				popup_modal_data[0].title			= $('#popup_title').val();
				popup_modal_data[0].color		= $('#popup_color').val();
				popup_modal_data[0].bgcolor		= $('#popup_bgcolor').val();
				popup_modal_data[0].buttons	= $('#popup_buttons').val();
				popup_modal_data[0].poptitle	= $('#post-body-content #title').val();
				popup_modal_data[0].body		= $('#content_ifr').contents().find('html').html();
				$('#wpwrap').css('visibility', 'hidden');
				popupModal();
			});
			$('.metabox-prefs .color-picker').wpColorPicker();
			$('.metabox-prefs .wp-picker-default').attr('value','Previous');
			$('.metabox-prefs .date-picker').datepicker({dateFormat:'mm-dd-yy'});
			if ($('#popup_disable').prop('checked')) {
				$('.metabox-prefs .wp-picker-container').css('display','none');
			}
			$('#popup_disable').click( function(event) {
				if ($(this).prop('checked')) {
					$('.metabox-prefs .metabox-options').css('opacity','0.4');
					$('.metabox-prefs .metabox-options input, .metabox-prefs .metabox-options select').prop( "disabled", true );
					$('.metabox-prefs .wp-picker-container').css('display','none');
					$('.popup-modal-test').prop( "disabled", true );
				} else {
					$('.metabox-prefs .metabox-options').css('opacity','1');
					$('.metabox-prefs .metabox-options input, .metabox-prefs .metabox-options select').prop( "disabled", false );
					$('.metabox-prefs .wp-picker-container').css('display','block');
					$('.popup-modal-test').prop( "disabled", false );
				}
			});
		});
	}
})( jQuery );

function setCookie(cname, expires) {
	var cvalue = 'Next:';
	if (expires>0) {
		var cookieDate = new Date;
		cookieDate.setTime(cookieDate.getTime()+(expires*3600000));
		expires = " expires=" + cookieDate.toGMTString() + ";";
		cvalue += cookieDate.toLocaleDateString()+'-'+cookieDate.toLocaleTimeString();
	} else {
		expires = "";
		cvalue += 'session';
	}
	var hostnameParts = location.hostname.split('.');
	var secondLevelDomain = hostnameParts.slice(-2).join('.');
	document.cookie = cname + "=" + cvalue + ";" + expires + " path=/; domain=." + secondLevelDomain;
	//document.cookie = cname + "=" + cvalue + ";" + expires + " path=/; domain=" + location.hostname;
}

function getCookie(cname) {
	var name = cname + "=";
	var ca = document.cookie.split(';');
	for(var i=0; i<ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1);
		if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
	}
	return false;
}