jQuery(document).ready(function ($)
{
    $('body').on('submit', '.subscribe-form', function (e)
    {
        e.preventDefault();
        var $el = $(this);
        var loading = '<p><img src="' + WPMailjet.loadingImg + '" alt="Please wait..."></p>';// Loading state
        var $res = $el.parent().find(".response").html(loading);// Clear previous messages
        $.post(WPMailjet.ajaxurl, $el.serialize(), function (data)
        {
            $res.html(data);
        });
    });

    $('body').on('click', '.widget-control-close', function (e)
    {
        var $res = $(this).closest('form').find(".mailjet_subscribe_response");
        if (jQuery.type($res.html()) !== undefined)
        {
            $res.hide();
        }
    });
});