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

        $('.mailjet_row [scope=row]').closest('th').hide();

        /**
         * Show text input if domain name is selected in the "From address" dropdown
         */
        function showExtraFromEmailInput($el) {
            if ($el.val() == undefined) {
				return;
			}
			if ($el.val().indexOf('*') >= 0) {
				var hiddenEmailExtra = $('#mailjet_from_email_extra_hidden').val();
				if (hiddenEmailExtra == undefined) {
                    hiddenEmailExtra = '';
				}
                $('#mailjet_from_email_fields .fromFldGroup').prepend('<input type="text" id="mailjet_from_email_extra" name="mailjet_from_email_extra" value="' + hiddenEmailExtra + '" required="required" placeholder="Enter your email name" />');
			} else {
                $('#mailjet_from_email_extra').remove();
			}
        };
        $('select[name="mailjet_from_email"]').change(function (e) {
            showExtraFromEmailInput($(this));
		});
		showExtraFromEmailInput($('select[name="mailjet_from_email"]'));


		// Show / Hide Initial Sync options div
        $('.mailjet_sync_options_div').show();
        if($('input[name="activate_mailjet_sync"]').prop('checked') !== true){
            $('.mailjet_sync_options_div').hide();
            $('#activate_mailjet_initial_sync').prop('checked', false);
        }
        $('input[name="activate_mailjet_sync"]').click(function () {
            $('.mailjet_sync_options_div').toggle('slow');
            $('#activate_mailjet_initial_sync').prop('checked', $('input[name="activate_mailjet_sync"]').prop('checked'));
        });

        // Show / Hide Comment Authors Sync div
        $('.mailjet_sync_comment_authors_div').hide();
        if($('input[name="activate_mailjet_comment_authors_sync"]').prop('checked') === true){
            $('.mailjet_sync_comment_authors_div').show();
        }
        $('input[name="activate_mailjet_comment_authors_sync"]').click(function () {
            $('.mailjet_sync_comment_authors_div').toggle('slow');
        });

		// Create new Contact List popup
	   	$(function() {
            $('#create_contact_list').on('click', function(event) {
                event.preventDefault();
                $('.pop').slideToggle('slow');
                $('#create_contact_list').hide();
                $('#createContactListImg').hide();
                $('#initialContactListsSubmit').hide();
                return false;
            });
            $('.closeCreateList').on('click', function(event) {
                event.preventDefault();
                $('.pop').slideToggle('slow');
                $('#create_contact_list').show();
                $('#createContactListImg').show();
                $('#initialContactListsSubmit').show();
                return false;
            });

        });



        // Change settings menu links images on hover
        $('.settingsMenuLink a').hover(
            function(){
                $(this).addClass('hover');
                var imgId = $(this).data('img_id');
                if ($(this).parent().hasClass('settingsMenuLink1')) {
                    $('.' + imgId).css({fill:"#FFFFFF"});
                } else {
                    $('.' + imgId).css({fill:"#19BC9C"});
                }
            },
            function(){
                $(this).removeClass('hover');
                var imgId = $(this).data('img_id');
                if (!$(this).hasClass('active')) {
                    if ($(this).parent().hasClass('settingsMenuLink1')) {
                        $('.' + imgId).css({fill:"#FFFFFF"});
                    } else {
                        $('.' + imgId).css({fill:"#000000"});
                    }
                }
            }
        );


    });
})( jQuery );

const mjInitShowHide = () => {
    
    const btn = document.querySelectorAll('.mj-toggleBtn');
    const expanded = document.querySelectorAll('.mj-show');

    if (expanded && expanded.length > 0) {
        expanded.forEach(function(el) {
            el.style.minHeight = `${el.scrollHeight}px`;
        });
    }
    
    if (btn && btn.length > 0) {
        btn.forEach(function(el) {
            const target = document.querySelector(`#${el.dataset.target}`);
            const isHidden = () => {
                const classes = target.className.split(' ');
                return classes.indexOf('mj-hide') >= 0;
            }

            el.addEventListener("click", function() {
                isHidden() ?
                    mjShow(target, btn)
                :
                    mjHide(target, btn);
            });
        });
    }
}

let transitionTimeout;
    
function deleteHeight(el) {
    transitionTimeout = window.setTimeout(function() {
        el.style.height = ''
    }, 1000);
}

function cleardeleteHeight() {
    window.clearTimeout(transitionTimeout);
}

function mjShow(target, btn) {
    cleardeleteHeight();

    const transitionTime = getComputedStyle(target)['transition-duration'];
    console.log(transitionTime);

    target.style.minHeight = 0;
    target.style.minHeight = `${target.scrollHeight}px`;
    deleteHeight(target);

    target.classList.remove('mj-hide');
    target.classList.add('mj-show');
    btn && btn.classList.add('mj-active');
    
}

function mjHide(target, btn) {
    cleardeleteHeight();
    target.style.height = `${target.scrollHeight}px`;
    target.style.height = '0';
    target.style.minHeight = '0';

    target.classList.remove('mj-show');
    target.classList.add('mj-hide');
    btn && btn.classList.remove('mj-active');
}

const mjSelect = () => {
    const allSelects = document.querySelectorAll('.mj-select');
    if (allSelects && allSelects.length > 0) {
        allSelects.forEach(function(select) {
            const wrapper = document.createElement('div');
            wrapper.classList.add('mj-select-wrapper');
            select.parentNode.insertBefore(wrapper, select);
            wrapper.appendChild(select);
            const selectValue = () => select.querySelector("option:checked").textContent
            wrapper.setAttribute('data-value', selectValue());

            select.addEventListener("change", function() {
                wrapper.setAttribute('data-value', selectValue());
            });
            select.addEventListener("focus", function() {
                wrapper.classList.add('mj-select-focus');
            });
            select.addEventListener("blur", function() {
                wrapper.classList.remove('mj-select-focus');
            });
        });
    }
}

const mjSendingSettings = () => {
    /**
     * disable SSL checkbox if port != 465
     */
    const portSelect = document.querySelector('.mjSettings #mailjet_port');
    const getPort = () => portSelect ? portSelect.value : null;
    const sslBox = document.querySelector('.mjSettings #mailjet_ssl');
    const sslLabel = sslBox.parentElement.nodeName == "LABEL" ? sslBox.parentElement : null
    
    const disableSSL = () => {
        if (getPort() !== "465") {
            sslBox.checked = false;

            sslBox.setAttribute("disabled", "disabled");
            sslLabel && sslBox.parentElement.classList.add('mj-disabled');
        }
        else {
            sslBox.removeAttribute("disabled");
            sslLabel && sslBox.parentElement.classList.remove('mj-disabled');
        }
    }

    if (portSelect && sslBox) {
        portSelect.addEventListener("change", function() {        
            disableSSL();
        })
        disableSSL();
    }

    /**
     * Show Sending email through MJ form
     */
    const mjEnabledBox = document.querySelector('.mjSettings #mailjet_enabled');
    const enabledForm = document.querySelector('.mjSettings #enable_mj_emails');
    const mjEnebledRequiredFlds = enabledForm.querySelectorAll('[required]');
    
    mjEnabledBox.addEventListener("change", function() {
        if (this.checked) {
            mjShow(enabledForm);
            mjEnebledRequiredFlds.forEach(function(fld) {
                fld.setAttribute('required', true);
            });
        } else {
            mjHide(enabledForm);
            mjEnebledRequiredFlds.forEach(function(fld) {
                fld.removeAttribute('required');
            });
        }
    })

    /**
     * Show Test email form
     */
    if (sslBox) {
        const btnTest = document.querySelector('.mjSettings #mailjet_test');
        sslBox.addEventListener("change", function() {
            this.checked ?
                mjShow(btnTest)
            :
            mjHide(btnTest)
        })
    }
}

const mjAdmin = () => {
    mjInitShowHide();
    mjSelect();
    document.querySelector('body').classList.contains('admin_page_mailjet_sending_settings_page');
    mjSendingSettings();
}

document.addEventListener('readystatechange', event => {
    if (event.target.readyState === "complete") {
        mjAdmin();
    }
});
