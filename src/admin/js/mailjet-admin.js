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

        $('#copy_properties').on("click", function () {
            const text = document.querySelector('#cf7_contact_properties');
            text.disabled = false;
            text.select();
            text.disabled = true;
            document.execCommand("copy");
        });

        // Copy Subscription widget shortcode
        $('.copy_mailjet_shortcode').on("click", function () {
            // Hide new property inputs
            const text_shortcode = document.querySelector('#' + $(this).attr('data-input_id'));
            text_shortcode.disabled = false;
            text_shortcode.select();
            text_shortcode.disabled = true;
            document.execCommand("copy");
        });


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
                if (!document.querySelector('#mailjet_from_email_extra')) {
                    $('#mailjet_from_email_fields .fromFldGroup').prepend('<input type="text" id="mailjet_from_email_extra" name="mailjet_from_email_extra" value="' + hiddenEmailExtra + '" required="required" />');
                }
            } else {
                $('#mailjet_from_email_extra').remove();
            }
        }
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
            const select = allSelects[i];
            const wrapper = document.createElement('div');
            wrapper.classList.add('mj-select-wrapper');
            select.parentNode.insertBefore(wrapper, select);
            wrapper.appendChild(select);
            function selectValue() {
                return select.querySelector("option:checked").textContent
            }
            if (select.querySelector("option:checked")) {
                wrapper.setAttribute('data-value', selectValue());
                select.addEventListener("change", function () {
                    wrapper.setAttribute('data-value', selectValue());
                });
            }
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

function mjCF7Subscription() {
    const activateCF7IntegrationBox = document.querySelector('#activate_mailjet_cf7_integration');
    const cf7ActicateIntegrationForm = document.querySelector('#activate_mailjet_cf7_form');

    if (activateCF7IntegrationBox === null || activateCF7IntegrationBox === undefined) {
        return false;
    }

    activateCF7IntegrationBox.addEventListener("change", function () {
        this.checked === true ? mjShow(cf7ActicateIntegrationForm) : mjHide(cf7ActicateIntegrationForm);
    });

    const saveButton = document.getElementById('integrationsSubmit');
    const cf7email = document.getElementById('cf7_email');

    saveButton.addEventListener("click", function (e) {
        if(activateCF7IntegrationBox.checked === true && cf7email.value === '') {
            cf7email.className+= ' mj-missing-required-input';
            e.preventDefault();
            return false;
        }

    });
}

function mjWooSubscription() {

    /**
     * Show / Hide WooCommerce Integration Activate div
     */
    const activateWooIntegrationBox = document.querySelector('#activate_mailjet_woo_integration');
    const wooActicateIntegrationForm = document.querySelector('#activate_mailjet_woo_form');
    const wooActicateIntegrationCheckbox = document.querySelector('#activate_mailjet_woo_checkbox');
    const wooActicateIntegrationSubbox= document.querySelector('#mailjet_woo_sub_letter');
    const wooActicateIntegrationBannerbox = document.querySelector('#activate_mailjet_woo_bannerbox');
    const wooActicateIntegrationBanner= document.querySelector('#mailjet_woo_sub_banner');


    if (activateWooIntegrationBox === null || activateWooIntegrationBox === undefined) {
        return false;
    }
    activateWooIntegrationBox.addEventListener("change", function () {
        if (wooActicateIntegrationForm.classList.contains('mj-hide')){
            mjShow(wooActicateIntegrationForm);
        } else {
            mjHide(wooActicateIntegrationForm);
        }
    });
    wooActicateIntegrationCheckbox.addEventListener("change", function () {
        if (wooActicateIntegrationSubbox.classList.contains('mj-hide')){
            mjShow(wooActicateIntegrationSubbox);
        } else {
            mjHide(wooActicateIntegrationSubbox);
        }
    });
    wooActicateIntegrationBannerbox.addEventListener("change", function () {
        if (wooActicateIntegrationBanner.classList.contains('mj-hide')){
            mjShow(wooActicateIntegrationBanner);
        } else {
            mjHide(wooActicateIntegrationBanner);
        }
    });
}

function mjSendingSettings() {
    /**
     * disable SSL checkbox if port != 465 && != 587 && != 588
     */
    const portSelect = document.querySelector('.mjSettings #mailjet_port');
    function getPort() {
        return portSelect ? portSelect.value : null;
    }
    const sslBox = document.querySelector('.mjSettings #mailjet_ssl');
    const sslLabel = sslBox.parentElement.nodeName === "LABEL" ? sslBox.parentElement : null;

    function disableSSL() {
        if (getPort() !== "465" && getPort() !== "587" && getPort() !== "588") {
            sslBox.checked = false;
            sslBox.setAttribute("disabled", "disabled");
            sslLabel && sslBox.parentElement.classList.add('mj-disabled');
        } else {
            sslBox.removeAttribute("disabled");
            sslLabel && sslBox.parentElement.classList.remove('mj-disabled');
        }
    }

    function changeEncryption() {
        if (sslBox.checked == true) {
            if (getPort() === "587" || getPort() === "588") {
                sslBox.value = 'tls';
            } else if (getPort() === "465") {
                sslBox.value = 'ssl';
            }
        }
    }

    if (portSelect && sslBox) {
        portSelect.addEventListener("change", function () {
            disableSSL();
            changeEncryption();
        });
        disableSSL();
        changeEncryption();
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
        || document.querySelector('body.admin_page_mailjet_subscription_options_page')
        || document.querySelector('body.admin_page_mailjet_integrations_page')) {
        mjSubscription();
    }
    if (document.querySelector('body.admin_page_mailjet_integrations_page')) {
        mjWooSubscription();
        mjCF7Subscription();
    }
    document.querySelector('body.admin_page_mailjet_sending_settings_page') && mjSendingSettings();
}
document.addEventListener('readystatechange', function (event) {
    if (event.target.readyState === "complete") {
        mjAdmin();
    }
});
