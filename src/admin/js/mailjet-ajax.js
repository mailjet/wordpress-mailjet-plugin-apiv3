function ajaxResync() {
    var data = {
        'action': 'resync_mailjet',
        'data': 'test uspeshen'
    };

    jQuery.ajax({
        type:"POST",
        url: ajaxurl,
        data: data,
        success:function(data){
            if (data.success){
                alert(data.data.message);
            } else {
                alert('error has occurred!');
            }
        },
        error: function(errorThrown){
            alert(errorThrown);
        }

    });
}