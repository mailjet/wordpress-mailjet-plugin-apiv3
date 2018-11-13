(function ($) {
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
        }
        ;
        $('select[name="mailjet_from_email"]').change(function (e) {
            showExtraFromEmailInput($(this));
        });
        showExtraFromEmailInput($('select[name="mailjet_from_email"]'));


        // Change settings menu links images on hover
//        $('.settingsMenuLink a').hover(
//                function () {
//                    $(this).addClass('hover');
//                    var imgId = $(this).data('img_id');
//                    if ($(this).parent().hasClass('settingsMenuLink1')) {
//                        $('.' + imgId).css({fill: "#FFFFFF"});
//                    } else {
//                        $('.' + imgId).css({fill: "#19BC9C"});
//                    }
//                },
//                function () {
//                    $(this).removeClass('hover');
//                    var imgId = $(this).data('img_id');
//                    if (!$(this).hasClass('active')) {
//                        if ($(this).parent().hasClass('settingsMenuLink1')) {
//                            $('.' + imgId).css({fill: "#FFFFFF"});
//                        } else {
//                            $('.' + imgId).css({fill: "#000000"});
//                        }
//                    }
//                }
//        );

    });
})(jQuery);

function mjInitShowHide() {

    const btn = document.querySelectorAll('.mj-toggleBtn');
    const expanded = document.querySelectorAll('.mj-show');
    const collapsed = document.querySelectorAll('.mj-hide');

    if (expanded && expanded.length > 0) {
        for (var i = 0; i < expanded.length; i++) {
            el = expanded[i];
            el.style.minHeight = el.scrollHeight + 'px';
        }
    }
    if (collapsed && collapsed.length > 0) {
        for (var i = 0; i < collapsed.length; i++) {
            el = collapsed[i];
            el.style.height = '0';
        }
    }

    if (btn && btn.length > 0) {
        for (var i = 0; i < btn.length; i++) {
            el = btn[i];
            const target = document.querySelector('#' + el.dataset.target);
            function isHidden() {
                const classes = target.className.split(' ');
                return classes.indexOf('mj-hide') >= 0;
            }

            el.addEventListener("click", function () {
                isHidden() ? mjShow(target, el) : mjHide(target, el);
            });
        }
    }
}

let transitionTimeout;

function deleteHeight(el, delay) {
    transitionTimeout = window.setTimeout(function () {
        el.style.height = '';
    }, delay);
}

function cleardeleteHeight() {
    window.clearTimeout(transitionTimeout);
}

function mjShow(target, btn) {
    cleardeleteHeight();

    target.style.minHeight = '0';
    target.style.minHeight = target.scrollHeight + 'px';

    target.classList.remove('mj-hide');
    target.classList.add('mj-show');
    btn && btn.classList.add('mj-active');

    const targetStyles = getComputedStyle(target);
    const transitionIndex = targetStyles['transition-property'].replace(/\s+/g, '').split(',').indexOf('min-height');
    const cssTransitionTime = targetStyles['transition-duration'].split(',')[transitionIndex];
    const jsTransitionTime = cssTransitionTime.indexOf('ms') > 0 ? parseFloat(cssTransitionTime) : parseFloat(cssTransitionTime) * 1000;
    deleteHeight(target, jsTransitionTime);
}

function mjHide(target, btn) {
    cleardeleteHeight();
    target.style.height = target.scrollHeight + 'px';
    target.style.height = '0';
    target.style.minHeight = '0';

    target.classList.remove('mj-show');
    target.classList.add('mj-hide');
    btn && btn.classList.remove('mj-active');
}

function mjSelect() {
    const allSelects = document.querySelectorAll('.mj-select');
    if (allSelects && allSelects.length > 0) {

        for (var i = 0; i < allSelects.length; i++) {
            select = allSelects[i];
            const wrapper = document.createElement('div');
            wrapper.classList.add('mj-select-wrapper');
            select.parentNode.insertBefore(wrapper, select);
            wrapper.appendChild(select);
            function selectValue() {
                return select.querySelector("option:checked").textContent
            }
            wrapper.setAttribute('data-value', selectValue());

            select.addEventListener("change", function () {
                wrapper.setAttribute('data-value', selectValue());
            });
            select.addEventListener("focus", function () {
                wrapper.classList.add('mj-select-focus');
            });
            select.addEventListener("blur", function () {
                wrapper.classList.remove('mj-select-focus');
            });
        }
    }
}

function mjSubscription() {
    /**
     * Handles Contact List form's display
     */
    const cancelCLBtn = document.querySelector('#cancel_create_list');
    cancelCLBtn && cancelCLBtn.addEventListener("click", function () {
        document.querySelector('#create_contact_list').click();
    });

    /**
     * Show / Hide Initial Sync options div
     */
    const autoSubscrBox = document.querySelector('#activate_mailjet_sync');
    const autoSubscrForm = document.querySelector('#activate_mailjet_sync_form');

    if (autoSubscrBox === null || autoSubscrBox === undefined) {
        return false;
    }
    autoSubscrBox.addEventListener("change", function () {
        this.checked === true ? mjShow(autoSubscrForm) : mjHide(autoSubscrForm);
    });

    /**
     * Show / Hide Comment Authors Sync div
     */
    const contactListBox = document.querySelector('#activate_mailjet_comment_authors_sync');
    const contactList = document.querySelector('#comment_authors_contact_list');

    if (contactListBox === null || contactListBox === undefined) {
        return false;
    }
    contactListBox.addEventListener("change", function () {
        this.checked === true ? mjShow(contactList) : mjHide(contactList);
    });
}

function mjSendingSettings() {
    /**
     * disable SSL checkbox if port != 465
     */
    const portSelect = document.querySelector('.mjSettings #mailjet_port');
    function getPort() {
        return portSelect ? portSelect.value : null;
    }
    const sslBox = document.querySelector('.mjSettings #mailjet_ssl');
    const sslLabel = sslBox.parentElement.nodeName === "LABEL" ? sslBox.parentElement : null;

    function disableSSL() {
        if (getPort() !== "465") {
            sslBox.checked = false;
            sslBox.setAttribute("disabled", "disabled");
            sslLabel && sslBox.parentElement.classList.add('mj-disabled');
        } else {
            sslBox.removeAttribute("disabled");
            sslLabel && sslBox.parentElement.classList.remove('mj-disabled');
        }
    }
    if (portSelect && sslBox) {
        portSelect.addEventListener("change", function () {
            disableSSL();
        });
        disableSSL();
    }
    /**
     * Show Sending email through MJ form
     */
    const mjEnabledBox = document.querySelector('.mjSettings #mailjet_enabled');
    const enabledForm = document.querySelector('.mjSettings #enable_mj_emails');
    const mjEnebledRequiredFlds = enabledForm.querySelectorAll('[required]');

    mjEnabledBox.addEventListener("change", function () {
        if (this.checked) {
            mjShow(enabledForm);
            mjEnebledRequiredFlds.forEach(function (fld) {
                fld.setAttribute('required', true);
            });
        } else {
            mjHide(enabledForm);
            mjEnebledRequiredFlds.forEach(function (fld) {
                fld.removeAttribute('required');
            });
        }
    });
    /**
     * Show Test email form
     */
//    if (sslBox) {
//        const btnTest = document.querySelector('.mjSettings #mailjet_test');
//        sslBox.addEventListener("change", function () {
//            this.checked ? mjShow(btnTest) : mjHide(btnTest);
//        });
//    }
}
function mjAdmin() {
    mjInitShowHide();
    mjSelect();
    if (document.querySelector('body.admin_page_mailjet_initial_contact_lists_page')
            || document.querySelector('body.admin_page_mailjet_subscription_options_page')) {
        mjSubscription();
    }
    document.querySelector('body.admin_page_mailjet_sending_settings_page') && mjSendingSettings();
}
document.addEventListener('readystatechange', function (event) {
    if (event.target.readyState === "complete") {
        mjAdmin();
    }
});