(function ($) {
    "use strict";
    $(function () {

        $(document).on('submit', 'form#mailjetSubscriptionForm', function (event) {
            event.preventDefault();

            const form = $(this);
            const message = $('.mailjet_widget_form_message');
            $('.mj_form_property').removeClass('has-error');

            jQuery.ajax({
                url : mjWidget.ajax_url,
                type : 'post',
                data : form.serializeArray(),
                success : function(response) {
                    try {
                        var data = JSON.parse(response);

                        if (data.prop_errors) {
                            $.each(data.prop_errors, function() {
                                var propInput = $('.mj_form_property[name="properties['+this+']"]');
                                propInput.addClass('has-error');
                            });
                        }
                    } catch (e) {
                        message.text(response);
                    }
                },
                error : function(err) {
                    message.text('An error occurred.');
                }
            });
        });
    });
}(jQuery));