<?php

/*
Plugin Name:	Mailjet for Wordpress
Version:		3.2.2
Plugin URI:		https://www.mailjet.com/plugin/wordpress.htm
Description:	Use mailjet SMTP to send email, manage lists and contacts within wordpress
Author:			Mailjet SAS
Author URI:		http://www.mailjet.com/
*/

/**
 * Copyright 2014  MAILJET  (email : plugins@mailjet.com)
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

# Include required files
require('api/mailjet-api-v3.php');
require('api/mailjet-api-v1.php');
require('view/options-form.php');
require('mailjet-class.php');
require('mailjet-options.php');
require('mailjet-widget.php');
require('mailjet-api-strategy.php');
require('mailjet-utils.php');


# Define mailjet options object
$optionsMj = new WP_Mailjet_Options();


# Check if the plugin is setted up properly
if (get_option('mailjet_password') && get_option('mailjet_username'))
{
	global $phpmailer;
	$MailjetApi = new WP_Mailjet_Api(get_option('mailjet_username'), get_option('mailjet_password'));

	if (!is_object( $phpmailer ) || !is_a( $phpmailer, 'PHPMailer'))
	{
		require_once ABSPATH . WPINC . '/class-phpmailer.php';
		require_once ABSPATH . WPINC . '/class-smtp.php';

		$phpmailer = new PHPMailer();
	}

	$WPMailjet = new WP_Mailjet($MailjetApi, $phpmailer);
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
		add_user_meta($user_id, 'wp_mailjet_notice_ignore', 'true', TRUE);
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


/**
 *  Register Mailjet's widget
 */
function wp_mailjet_register_widgets()
{
	register_widget('WP_Mailjet_Subscribe_Widget');
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
	// If contact list is not selected, then do not show the extra fields
	if(get_option('mailjet_auto_subscribe_list_id'))
	{
		// Update the extra fields
		mailjet_subscribe_unsub_user_to_list(esc_attr(get_the_author_meta('mailjet_subscribe_ok', $user->ID)), $user->ID);
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
}


add_action ( 'personal_options_update', 'mailjet_my_save_extra_profile_fields' );
add_action ( 'edit_user_profile_update', 'mailjet_my_save_extra_profile_fields' );


/* Add cutom field to registration form */
if (get_option('mailjet_auto_subscribe_list_id'))
{
	add_action('register_form','show_mailjet_subscribe_field');
	add_action('user_register', 'register_extra_fields');
}


/**
 *  Show the Mailjet's subscribe form (this is part of a Widget)
 */
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


/**
 *  Set extra profile fields when the profile is saved
 */
function register_extra_fields( $user_id, $password = "", $meta = array() )
{
	$subscribe = filter_var($_POST ['mailjet_subscribe_ok'], FILTER_SANITIZE_NUMBER_INT);

	update_user_meta( $user_id, 'mailjet_subscribe_ok', $subscribe);
	mailjet_subscribe_unsub_user_to_list($subscribe, $user_id);
}


/**
 *  Subscribe or unsubscribe a wordpress user (admin, editor, etc.) in/from a Mailjet's contact list when the profile is saved
 */
function mailjet_subscribe_unsub_user_to_list($subscribe, $user_id)
{
	if (get_option('mailjet_password') && get_option('mailjet_username'))
	{
		$user = get_userdata( $user_id );
		$MailjetApi = new WP_Mailjet_Api(get_option('mailjet_username'), get_option('mailjet_password'));

		if ($subscribe && $list_id = get_option('mailjet_auto_subscribe_list_id'))
		{
			// Add the user to a contact list
			$response = $MailjetApi->addContact(array(
				'Email'		=> (isset($_POST['email']))?$_POST['email']:$user->data->user_email,
				'ListID'	=> $list_id
			));
		}
		elseif (!$subscribe && $list_id = get_option('mailjet_auto_subscribe_list_id'))
		{
			// Remove a user from a contact lists
			$MailjetApi->removeContact(array(
				'Email'		=> (isset($_POST['email']))?$_POST['email']:$user->data->user_email,
				'ListID'	=> $list_id
			));
		}
	}
}


/**
 *  Update extra profile fields when the profile is saved
 */
function mailjet_my_save_extra_profile_fields( $user_id )
{
	if ( !current_user_can( 'edit_user', $user_id ) )
		return FALSE;

	$subscribe = filter_var($_POST ['mailjet_subscribe_ok'], FILTER_SANITIZE_NUMBER_INT);

	update_usermeta($user_id, 'mailjet_subscribe_ok', $subscribe);
	mailjet_subscribe_unsub_user_to_list($subscribe, $user_id);
}


load_plugin_textdomain ('wp-mailjet', FALSE, dirname (plugin_basename(__FILE__)) . '/i18n');
