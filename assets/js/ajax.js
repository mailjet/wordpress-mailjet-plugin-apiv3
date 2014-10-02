jQuery(document).ready(function($)
{
	$('.subscribe-form').submit(function(e)
	{
		e.preventDefault();
		var $el  = $(this);
		var loading = '<p><img src="'+WPMailjet.loadingImg+'" alt="Please wait..."></p>';// Loading state
		var $res = $el.parent().find(".response").html(loading);// Clear previous messages
		$.post(WPMailjet.ajaxurl, $el.serialize(), function(data)
		{
			$res.html(data);
		});
	})
});