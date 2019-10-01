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

function displaySyncListChoice() {
    mjHide(document.getElementById('div-for-ajax'));
    mjShow(document.getElementById('changeSyncList'));
}