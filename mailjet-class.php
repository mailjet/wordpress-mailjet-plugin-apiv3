<?php

/**
 * Mailjet Wordpress Plugin
 *
 * @author		Mailjet
 * @link		http://www.mailjet.com/
 *
 */

class WP_Mailjet
{
	protected $api;
	protected $phpmailer;

	public function __construct($api, $phpMailer)
	{
		// Set Plugin Path
		$this->pluginPath = dirname(__FILE__);

		// Set Plugin URL
		$this->pluginUrl = WP_PLUGIN_URL . dirname(__FILE__);

		$this->api = (is_object($api)) ? $api : new WP_Mailjet_Api(get_option('mailjet_username'), get_option('mailjet_password'));
		$this->phpmailer = $phpMailer;

		add_action('phpmailer_init', array($this, 'phpmailer_init_smtp'));
		add_action('admin_menu', array($this, 'display_menu'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
	}

	public function enqueue_scripts()
	{
		wp_register_script('mailjet_js', plugins_url('/assets/js/mailjet.js', __FILE__), array('jquery'));
		wp_enqueue_script( 'mailjet_js');
	}

	public function display_menu()
	{
		if (function_exists('add_submenu_page'))
		{
			if (
				current_user_can('administrator') 
					|| 
				(current_user_can('editor') && get_option('mailjet_access_editor') == 1)
					||
				(current_user_can('author') && get_option('mailjet_access_author') == 1)
					||
				(current_user_can('contributor') && get_option('mailjet_access_contributor') == 1)
					||
				(current_user_can('subscriber') && get_option('mailjet_access_subscriber') == 1)
			) {
				add_submenu_page('wp_mailjet_options_top_menu', __('Manage your Mailjet lists', 'wp-mailjet'), __('Lists', 'wp-mailjet'), 'read', 'wp_mailjet_options_contacts_menu', array($this, 'show_contacts_menu'));
				add_submenu_page('wp_mailjet_options_top_menu', __('Manage your Mailjet campaigns', 'wp-mailjet'), __('Campaigns', 'wp-mailjet'), 'read', 'wp_mailjet_options_campaigns_menu', array($this, 'show_campaigns_menu'));
				add_submenu_page('wp_mailjet_options_top_menu', __('View your Mailjet statistics', 'wp-mailjet'), __('Statistics', 'wp-mailjet'), 'read', 'wp_mailjet_options_stats_menu', array($this, 'show_stats_menu'));
			}
		}
	}

	function phpmailer_init_smtp (PHPMailer $phpmailer)
	{
		if (!get_option('mailjet_enabled') || 0 == get_option('mailjet_enabled'))
			return;

		$phpmailer->Mailer = 'smtp';
		$phpmailer->SMTPSecure = get_option('mailjet_ssl');
	
		$phpmailer->Host = $this->api->mj_host;
		$phpmailer->Port = get_option('mailjet_port');

		$phpmailer->SMTPAuth = TRUE;
		$phpmailer->Username = get_option('mailjet_username');
		$phpmailer->Password = get_option('mailjet_password');

		$from_email = (get_option('mailjet_from_email') ? get_option('mailjet_from_email') : get_option('admin_email'));
		$phpmailer->From = $from_email;
		$phpmailer->Sender = $from_email;
		$phpmailer->AddCustomHeader($this->api->mj_mailer);
	}

	private function _get_auth_token()
	{
		// Get the
		$token = $this->api->getAuthToken(array(
			'APIKey'		=> get_option('mailjet_username'), // Use any API Key from your Sub-accounts
			'SecretKey'		=> get_option('mailjet_password'),
			'MailjetToken'	=> get_option('mailjet_token' . $_SERVER['REMOTE_ADDR'])
		));

		// Return FALSE if there is token
		if(isset($token->Status) && $token->Status == 'ERROR')
			return FALSE;

		return $token;
	}

	public function show_campaigns_menu()
	{
		echo '<div class="wrap"><div class="icon32"><img src="' . plugin_dir_url(__FILE__) . '/assets/images/mj_logo_med.png' . '" /></div><h2>';
		echo __('Campaigns', 'wp-mailjet');
		echo'</h2></div>';
		echo '<iframe width="980px" height="1200" src="https://'.(($this->api->version == '0.1')?'www':(($this->api->version == 'REST')?'app':'www')).'.mailjet.com/campaigns?t='.$this->_get_auth_token().'&show_menu=none&u=WordPress-3.1&f=amc"></iframe>';
	}

	public function show_stats_menu()
	{
		echo '<div class="wrap"><div class="icon32"><img src="' . plugin_dir_url(__FILE__) . '/assets/images/mj_logo_med.png' . '" /></div><h2>';
		echo __('Statistics', 'wp-mailjet');
		echo'</h2></div>';
		echo '<iframe width="980px" height="1200" src="https://'.(($this->api->version == '0.1')?'www':(($this->api->version == 'REST')?'app':'www')).'.mailjet.com/stats?t='.$this->_get_auth_token().'&show_menu=none&u=WordPress-3.1&f=amc"></iframe>';		
	}

	public function show_contacts_menu()
	{
		echo '<div class="wrap"><div class="icon32"><img src="' . plugin_dir_url(__FILE__) . '/assets/images/mj_logo_med.png' . '" /></div><h2>';
		echo __('Contacts', 'wp-mailjet');
		echo'</h2></div>';
		echo '<iframe width="980px" height="1200" src="https://'.(($this->api->version == '0.1')?'www':(($this->api->version == 'REST')?'app':'www')).'.mailjet.com/contacts/lists?t='.$this->_get_auth_token().'&show_menu=none&u=WordPress-3.1&f=amc"></iframe>';		
	}
}