function subscribeMe(element) {
    let id = parseInt(jQuery(element).attr('data-order'), 10);
    let key = jQuery(element).attr('data-order-key');
    if (Number.isInteger(id) && key) {
        let data = {
            'action': 'mj_ajax_subscribe',
            'orderId': id,
            'orderKey': key,
            'nonce': mailjet.nonce,
        };
        ajax("POST", data, subscriptionHandler);
    }
}

function subscriptionHandler(response) {
    let container = jQuery('.mj-front-container');
    container.find('button').remove();
    container.find('span').text(response.message);
    if (response.success) {
        container.css({'background-color': '#81d68f', 'text-align': 'center'});
    } else {
        container.css({'background-color': '#f7a6a6', 'text-align': 'center'});
    }
}

function ajax(type, data, callback) {
    if (type.length === 0 || data.length === 0) {
        return false;
    }
    jQuery.ajax({
        type: type,
        url: mailjet.url,
        data: data,
        success: function (response) {
            if (typeof callback === 'function') {
                callback(response.data);
            }
        },
        error: function (errorThrown) {
            return false;
        }

    });
}