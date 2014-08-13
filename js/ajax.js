jQuery(document).ready(function($)
{
	$('.subscribe-form').submit(function(e)
	{
		e.preventDefault();
		var $el = $(this);
		$.post(WPMailjet.ajaxurl, $el.serialize(), function(data)
		{
			$el.parent().find(".response").html(data);
		});
	})
});