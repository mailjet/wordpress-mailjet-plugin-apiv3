(function ($) {
    "use strict";
    $(function () {

        $(document).on('submit', 'form#mailjetSubscriptionForm', function (event) {
            event.preventDefault();

            const form = $(this);
            const message = $('.mailjet_widget_form_message');
            jQuery.ajax({
                url : mjWidget.ajax_url,
                type : 'post',
                data : form.serializeArray(),
                success : function(response) {
                    message.text(response);
                },
                error : function(err) {
                    message.text('An error occurred.');
                }
            });
        });
    });
}(jQuery));