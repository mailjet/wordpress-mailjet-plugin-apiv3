<?php

/**
 * Mailjet Wordpress Plugin
 *
 * @author		Mailjet
 * @link		http://www.mailjet.com/
 *
 */

class WP_Mailjet_Utils
{
	public static function custom_notice($type, $message)
	{
		echo '<div class="' . $type . '"><p>' . $message . '</p></div>';
	}
}