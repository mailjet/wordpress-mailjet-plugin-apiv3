<?php

/*
Plugin Name:	Mailjet for Wordpress
Version:		3.0.1
Plugin URI:		https://www.mailjet.com/plugin/wordpress.htm
Description:	Use mailjet SMTP to send email, manage lists and contacts within wordpress
Author:			Mailjet SAS
Author URI:		http://www.mailjet.com/
*/

/**
 * Copyright 2013  MAILJET  (email : plugins@mailjet.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

require('mailjet-utils.php');
require('mailjet-api.php');
require('mailjet-class.php');
require('mailjet-options.php');
require('mailjet-widget.php');
require('views/options-form.php');

define ('MJ_HOST', 'in-v3.mailjet.com');
define ('MJ_MAILER', 'X-Mailer:WP-Mailjet/0.1');

$options = new WPMailjet_Options();

//Check plugin is set up properly
if (get_option('mailjet_password') && get_option('mailjet_username'))
{
	global $phpmailer;
	$MailjetApi = new Mailjet(get_option('mailjet_username'), get_option('mailjet_password'));

	if (!is_object( $phpmailer ) || !is_a( $phpmailer, 'PHPMailer' ))
	{
		require_once ABSPATH . WPINC . '/class-phpmailer.php';
		require_once ABSPATH . WPINC . '/class-smtp.php';

		$phpmailer = new PHPMailer();
	}

	$WPMailjet = new WPMailjet($MailjetApi, $phpmailer);
	add_action( 'widgets_init', 'wp_mailjet_register_widgets' );

}
elseif(get_option('mailjet_enabled') && (!get_option('mailjet_password') || !get_option('mailjet_username')))
{
	// Display a notice that can be dismissed
	add_action('admin_notices', 'wp_mailjet_admin_notice');
}

add_action('admin_init', 'wp_mailjet_notice_ignore');

function wp_mailjet_notice_ignore()
{
	global $current_user;
	$user_id = $current_user->ID;

	// If user clicks to ignore the notice, add that to their user meta
	if (isset($_GET['wp_mailjet_notice_ignore']) && '1' == $_GET['wp_mailjet_notice_ignore'])
		add_user_meta($user_id, 'wp_mailjet_notice_ignore', 'true', true);
}

function wp_mailjet_admin_notice()
{
	global $current_user ;
	$user_id = $current_user->ID;

	// Check that the user hasn't already clicked to ignore the message
	if (!get_user_meta($user_id, 'wp_mailjet_notice_ignore'))
	{
		echo '<div class="error"><p>';
		printf(__('The mailjet plugin is enabled but your credentials are not set. Please <a href="admin.php?page=wp_mailjet_options_top_menu" title="enable Mailjet plugin">do so now</a> to send your emails through <b>Mailjet</b> <a href="%1$s" style="display:block; float:right;">Hide Notice</a>', 'wp-mailjet'), 'admin.php?page=wp_mailjet_options_top_menu?wp_mailjet_notice_ignore=1');
		echo "</p></div>";
	}
}

function wp_mailjet_register_widgets()
{
	register_widget('MailjetSubscribeWidget');
}

/**
 * Display settings link on plugins page
 *
 * @param array $links
 * @param string $file
 * @return array
 */
function mailjet_settings_link($links, $file)
{
	if ($file != plugin_basename( __FILE__ ))
		return $links;

	$settings_link = '<a href="admin.php?page=wp_mailjet_options_top_menu">' . __( 'Settings', 'wp-mailjet' ) . '</a>';

	array_unshift( $links, $settings_link );

	return $links;
}

add_filter( 'plugin_action_links', 'mailjet_settings_link', 10, 2);

/* Add additional custom field */

add_action ( 'show_user_profile', 'my_show_extra_profile_fields' );
add_action ( 'edit_user_profile', 'my_show_extra_profile_fields' );

function my_show_extra_profile_fields($user)
{
?>
	<h3>Extra profile information</h3>
	<table class="form-table">
		<tr>
			<th><label for="mailjet_subscribe_ok"><?php _e('Subscribe')?></label></th>
			<td>
				<fieldset><legend class="screen-reader-text"><span><?php _e('Subscribe')?></span></legend>
				<label for="admin_bar_front">
					<input type="checkbox" name="mailjet_subscribe_ok" id="mailjet_subscribe_ok" value="1" <?php echo  (esc_attr( get_the_author_meta( 'mailjet_subscribe_ok', $user->ID ) ) ? 'checked="checked" ' : ''); ?>class="checkbox" />
					<?php _e('Subscribe to our newsletter')?>
				</label>
				</fieldset>
			</td>
		</tr>
	</table>
<?php
}

add_action ( 'personal_options_update', 'my_save_extra_profile_fields' );
add_action ( 'edit_user_profile_update', 'my_save_extra_profile_fields' );


/* Add cutom field to registration form */

if (get_option('mailjet_auto_subscribe_list_id'))
{
	add_action('register_form','show_mailjet_subscribe_field');
	add_action('user_register', 'register_extra_fields');
}

function show_mailjet_subscribe_field()
{
?>
	<p>
		<label>
			<input type="checkbox" name="mailjet_subscribe_ok" id="mailjet_subscribe_ok" value="1" <?php echo  (esc_attr( get_the_author_meta( 'mailjet_subscribe_ok', $user->ID ) ) ? 'checked="checked" ' : ''); ?>class="checkbox" />
			<?php _e('Subscribe to our newsletter')?>
		</label>
	</p>
<?php
}

function register_extra_fields ( $user_id, $password = "", $meta = array() )
{
	$subscribe = filter_var($_POST ['mailjet_subscribe_ok'], FILTER_SANITIZE_NUMBER_INT);
	
	update_user_meta( $user_id, 'mailjet_subscribe_ok', $subscribe);
	mailjet_subscribe_unsub_user_to_list($subscribe, $user_id);
}

function mailjet_subscribe_unsub_user_to_list($subscribe, $user_id)
{
	if (get_option('mailjet_password') && get_option('mailjet_username'))
	{
		$user = get_userdata( $user_id );
		$MailjetApi = new Mailjet(get_option('mailjet_username'), get_option('mailjet_password'));

		if ($subscribe && $list_id = get_option('mailjet_auto_subscribe_list_id'))
		{
			// Add the contact
			$params = array(
				'method' => 'POST',
				'Email' =>	$user->data->user_email
			);		
			$result = $MailjetApi->contact($params);
			
			// There is an error
			if(isset($result->StatusCode) && $result->StatusCode == '400')
				return false;
			
			// If no error, get the ID of the contact
			$contact_id = $result->Data[0]->ID;
			
			// Add the contact to a contact list
			$params = array(
				'method'	=> 'POST',
				'ContactID'	=> $contact_id,
				'ListID'	=> $list_id
			);		
			$result = $MailjetApi->listrecipient($params);			
			
			// Check if any error
			if(isset($result->StatusCode))
				return false;
		}
		elseif (!$subscribe && $list_id = get_option('mailjet_auto_subscribe_list_id'))
		{
			// Get the contact
			$params = array(
				'akid'          => $MailjetApi->_akid,
				'method'        => 'GET',
				'ContactEmail'  => $user->data->user_email
            );		
			$result = $MailjetApi->listrecipient($params);
            if($result->Count > 0) 
            {
                foreach($result->Data as $contact) 
                {
                    if($contact->IsUnsubscribed !== true)
                    {
                          $result = $MailjetApi->listrecipient(array(
                                'akid'    => $MailjetApi->_akid,
                                'method'   => 'PUT',
                                'ID'       => $contact->ID,
                                'IsUnsubscribed' => "true",
                                'UnsubscribedAt' => date("Y-m-d\TH:i:s\Z", time()),
                          ));
                    } 
                }
            }			
		}
	}
}

function my_save_extra_profile_fields( $user_id )
{
	if ( !current_user_can( 'edit_user', $user_id ) )
		return false;

	$subscribe = filter_var($_POST ['mailjet_subscribe_ok'], FILTER_SANITIZE_NUMBER_INT);

	update_usermeta($user_id, 'mailjet_subscribe_ok', $subscribe);
	mailjet_subscribe_unsub_user_to_list($subscribe, $user_id);
}

load_plugin_textdomain ('wp-mailjet', FALSE, dirname (plugin_basename(__FILE__)) . '/i18n');