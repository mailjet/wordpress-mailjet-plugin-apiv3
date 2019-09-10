function ajaxResync() {
    let data = {
        'action': 'resync_mailjet'
    };
    let msgDiv =  jQuery('#div-for-ajax');
    jQuery.post(ajaxurl, data, function (response) {
        msgDiv.html('');
        if (response.success){
            msgDiv.html(`<span>${response.data.message}&nbsp<a href="${response.data.url}">Mailjet contact list page</a></span>`);
        } else {
            msgDiv.html(`<span>Error has occurred! Please try again later. <a href="#" onclick="ajaxResync()">Resync contact list</a></span>`);
        }
    });

}

function loadLists() {
    let data = {
        'action': 'get_contact_lists_menu'
    };
    let select = jQuery('<select></select>').attr('id', 'mailjet_sync_list').attr('class', 'mj-select').attr('name', 'mailjet_sync_list');

    let optionsDiv =  jQuery('#contact_list');
    let selectDiv =  jQuery('<div></div>').attr('class', 'mj-select-wrapper').attr('id', 'mj-select-div');
    jQuery.post(ajaxurl, data, function (response) {
        if (response.success){
            jQuery.each( response.data.mailjetContactLists, function (key, value){
               if (value.IsDeleted === false){
                   let checked =  '';
                   if (response.data.mailjetSyncList === value.ID) {
                       selectDiv.attr('data-value', value.Name + ' (' + value.SubscriberCount + ')');
                       checked = 'selected="selected"';
                   }
                   select.append(`<option value="${value.ID}" ${checked}>${value.Name} (${value.SubscriberCount})</option>`);
               }
            });

            select.on('change',function ()   {
                let optionValue = jQuery('#'+jQuery(this).attr('id') + " option:selected").text();
                selectDiv.attr('data-value', optionValue);
            });
            optionsDiv.children().remove();
            selectDiv.append(select);
            optionsDiv.append(selectDiv);
        }
    });
}