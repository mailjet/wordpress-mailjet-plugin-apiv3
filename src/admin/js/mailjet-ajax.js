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
    let select = jQuery('<select></select>').attr('id', 'mailjet_sync_list').attr('class', 'mj-select').attr('name', 'mailjet_sync_list');
    let selectDiv =  jQuery('#contact_list');
    ajax("POST", data, function (response) {
        if (response.success){
            jQuery.each( response.data.mailjetContactLists, function (key, value){
               if (value.IsDeleted === false){
                   let checked =  '';
                   if (response.data.mailjetSyncList === value.ID) {
                       checked = 'selected="selected"';
                   };
                   select.append(`<option value="${value.ID}" ${checked}>${value.Name} (${value.SubscriberCount})</option>`)
               };

            });
            selectDiv.children('select').remove();
            selectDiv.append(select);
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