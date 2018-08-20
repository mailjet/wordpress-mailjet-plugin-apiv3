(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
    jQuery(document).ready(function ($) {

        $('[scope=row]').closest('th').hide();

		function showExtraFromEmailInput($el) {
			if ($el.val() == undefined) {
				return;
			}
			if ($el.val().indexOf('*') >= 0) {
				var hiddenEmailExtra = $('#mailjet_from_email_extra_hidden').val();
				if (hiddenEmailExtra == undefined) {
                    hiddenEmailExtra = '';
				}
                $('<input type="text" id="mailjet_from_email_extra" name="mailjet_from_email_extra" value="' + hiddenEmailExtra + '" placeholder="Enter your email address" />').insertBefore('#mailjet_from_email');
			} else {
                $('#mailjet_from_email_extra').remove();
			}
		};
		$('select[name="mailjet_from_email"]').change(function (e) {
			showExtraFromEmailInput($(this));
		});
		showExtraFromEmailInput($('select[name="mailjet_from_email"]'));


		// Show / Hide Sending options div
        $('.sending_options_div').hide();
        if($('input[name="mailjet_enabled"]').prop('checked') === true){
		   $('.sending_options_div').show();
       	}
        $('input[name="mailjet_enabled"]').click(function () {
            $('.sending_options_div').toggle('fast');
        });

        // Show / Hide Initial Sync options div
        $('.mailjet_sync_options_div').hide();
        if($('input[name="activate_mailjet_sync"]').prop('checked') === true){
            $('.mailjet_sync_options_div').show();
        }
        $('input[name="activate_mailjet_sync"]').click(function () {
            $('.mailjet_sync_options_div').toggle('fast');
        });

        // Show / Hide Comment Authors Sync div
        $('.mailjet_sync_comment_authors_div').hide();
        if($('input[name="activate_mailjet_comment_authors_sync"]').prop('checked') === true){
            $('.mailjet_sync_comment_authors_div').show();
        }
        $('input[name="activate_mailjet_comment_authors_sync"]').click(function () {
            $('.mailjet_sync_comment_authors_div').toggle('fast');
        });



        // Send test email popup
        function deselect(e) {
            $('.pop').slideFadeToggle(function() {
                e.removeClass('selected');
            });
        }
        $.fn.slideFadeToggle = function(easing, callback) {
            return this.animate({ opacity: 'toggle', height: 'toggle' }, 'fast', easing, callback);
        };

        $(function() {
            $('#mailjet_test').on('click', function() {
                if($(this).hasClass('selected')) {
                    deselect($(this));
                } else {
                    $(this).addClass('selected');
                    $('.pop').slideFadeToggle();
                }
                return false;
            });

            $('.close').on('click', function() {
                deselect($('#mailjet_test'));
                return false;
            });
        });


		// Create new Contact List popup
	   	$(function() {
            $('#create_contact_list').on('click', function() {
                if($(this).hasClass('selected')) {
                    deselect($(this));
                } else {
                    $(this).addClass('selected');
                    $('.pop').slideFadeToggle();
                }
                return false;
            });
            $('.closeCreateList').on('click', function() {
                deselect($('#create_contact_list'));
                return false;
            });

        });




    });
})( jQuery );
