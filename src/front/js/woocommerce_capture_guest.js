jQuery('input#billing_email').on('change', sendData);
jQuery('input#billing_first_name').on('change', sendData);
jQuery('input#billing_last_name').on('change', sendData);

function sendData() {
    var data = {
        billing_email		: jQuery('#billing_email').val(),
        billing_first_name	: jQuery('#billing_first_name').val(),
        billing_last_name	: jQuery('#billing_last_name').val(),
        action: 'save_guest_data'
    };
    if (data['billing_email'] != '') {
        jQuery.post(woocommerce_capture_guest_params.ajax_url, data, function(response) {});
    }
}
