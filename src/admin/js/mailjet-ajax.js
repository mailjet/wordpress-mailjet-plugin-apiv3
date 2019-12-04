function ajaxResync() {
    const data = {
        'action': 'resync_mailjet'
    };
    const msgDiv =  jQuery('#div-for-ajax');
    jQuery.post(ajaxurl, data, function (response) {
        msgDiv.html('');
        if (response.success){
            const splittedMsg = response.data.message.split("%s");
            msgDiv.html(`<span>${splittedMsg[0]}<a href="${response.data.url}">${response.data.url_string}</a>${splittedMsg[1]}</span>`);
        } else {
            msgDiv.html(`<span>Error has occurred! Please try again later. <a href="#" onclick="ajaxResync()">Resync contact list</a></span>`);
        }
    });

}

function displaySyncListChoice() {
    mjHide(document.getElementById('div-for-ajax'));
    mjShow(document.getElementById('changeSyncList'));
}