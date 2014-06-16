
/**
 * Created with JetBrains PhpStorm.
 * User: jonathan
 * Date: 5/25/12
 * Time: 4:07 PM
 * To change this template use File | Settings | File Templates.
 */

jQuery(document).ready(function($)
{
	showPorts = function($el)
	{
		$('#mailjet_port').empty()

		if ($el.attr('checked') == 'checked')
			$('#mailjet_port').append('<option value="465">465</option>');
		else
		{
			$('#mailjet_port')
				.append('<option value="25">25</option>')
				.append('<option value="587">587</option>')
				.append('<option value="588">588</option>')
				.append('<option value="80">80</option>');
		}
	}

	$('#addContact').on('click', function(e)
	{
		e.preventDefault();

		var contactInput = $('#firstContactAdded').clone();
		var $el = $(e.currentTarget);

		$el.before(contactInput);
	});

	$('select[name=action2]').change(function(e)
	{
		$('select[name=action]').val($(this).val());
	})

	$('#mailjet_ssl').change(function(e)
	{
		showPorts($(this));
	})

	showPorts($('#mailjet_ssl'));
});