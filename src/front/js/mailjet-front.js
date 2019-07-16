
function subscribeMe(element) {
    let data = {
        'action': 'subscribe_to_list'
    };

    let id = jQuery(element).data('order');

    if (!isNaN(id)){
        ajax("POST", data, subscriptionHandler);
    }

}

function subscriptionHandler(response) {
    if (response.success){
        jQuery.each( response.data.mailjetContactLists, function (key, value){
            if (value.IsDeleted === false){
                let checked =  '';
                if (response.data.mailjetSyncList === value.ID) {
                    selectDiv.attr('data-value', value.Name + ' (' + value.SubscriberCount + ')');
                    checked = 'selected="selected"';
                };
                select.append(`<option value="${value.ID}" ${checked}>${value.Name} (${value.SubscriberCount})</option>`)
            };

        });
        optionsDiv.children().remove();
        selectDiv.append(select);
        optionsDiv.append(selectDiv);
    }
}

function ajax(type, data, callback) {
    if (type.length === 0 || data.length === 0){
        return false;
    }

    console.log(mailjet.url);
    jQuery.ajax({
        type: type,
        url: ajaxurl,
        data: data,
        success:function(response){
            if (typeof callback === 'function' ){
                console.log('da');
                // callback(response);
            }else {
                console.log('ne');
                // return response
            }
        },
        error: function(errorThrown){
            return false;
        }

    });
}