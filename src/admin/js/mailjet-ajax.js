function ajaxResync() {
    let data = {
        'action': 'resync_mailjet'
    };
    let msgDiv =  jQuery('#div-for-ajax');
    ajax("POST", data, function (response) {
        msgDiv.html('');
        if (response.success){
            msgDiv.html(`<span>${response.data.message}&nbsp&nbsp <a href="#" onclick="loadLists()">Mailjet contact list page</a></span>`);
        } else {
            msgDiv.html(`<span>Error has occurred! Please try again later. &nbsp&nbsp<a href="#" onclick="loadLists()">Mailjet contact list page</a></span>`);
        }
    });

}

function loadLists() {
    let data = {
        'action': 'get_contact_lists'
    };
    let msgDiv =  jQuery('#div-for-ajax');
    ajax("POST", data, function (response) {
        alert(response);
        return false;
        msgDiv.html('');
        if (response.success){
            msgDiv.html(`<span>${response.data.message}&nbsp&nbsp <a href="#" onclick="loadLists()">Mailjet contact list page</a></span>`);
        } else {
            msgDiv.html(`<span>Error has occurred! Please try again later. &nbsp&nbsp<a href="#" onclick="loadLists()">Mailjet contact list page</a></span>`);
        }
    });
}

function ajax(type, data, callback) {
    if (type.length === 0 || data.length === 0){
        return false;
    }
    jQuery.ajax({
        type: type,
        url: ajaxurl,
        data: data,
        success:function(response){
            if (typeof callback === 'function' ){
                callback(response);
            }else {
                return response
            }
        },
        error: function(errorThrown){
            return false;
        }

    });
}