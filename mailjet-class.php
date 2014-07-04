<?php

/**
 * Mailjet Wordpress Plugin
 *
 * @author		Mailjet
 * @link		http://www.mailjet.com/
 *
 */

class WPMailjet
{
	protected $api;
	protected $phpmailer;

	public function __construct($api, $phpMailer)
	{
		// Set Plugin Path
		$this->pluginPath = dirname(__FILE__);

		// Set Plugin URL
		$this->pluginUrl = WP_PLUGIN_URL . dirname(__FILE__);

		$this->api = $api;
		$this->phpmailer = $phpMailer;

		add_action('phpmailer_init', array($this, 'phpmailer_init_smtp'));
		add_action('admin_menu', array($this, 'display_menu'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
	}

	public function enqueue_scripts()
	{
		wp_register_script('mailjet_js', plugins_url('/js/mailjet.js', __FILE__), array('jquery'));
		wp_enqueue_script( 'mailjet_js');
	}

	public function display_menu()
	{
		if (function_exists('add_submenu_page'))
		{
			add_submenu_page('wp_mailjet_options_top_menu', __('Manage your Mailjet lists', 'wp-mailjet'), __('Lists', 'wp-mailjet'), 'manage_options', 'wp_mailjet_options_contacts_menu', array($this, 'show_contacts_menu'));
			add_submenu_page('wp_mailjet_options_top_menu', __('Manage your Mailjet campaigns', 'wp-mailjet'), __('Campaigns', 'wp-mailjet'), 'manage_options', 'wp_mailjet_options_campaigns_menu', array($this, 'show_campaigns_menu'));
			add_submenu_page('wp_mailjet_options_top_menu', __('View your Mailjet statistics', 'wp-mailjet'), __('Statistics', 'wp-mailjet'), 'manage_options', 'wp_mailjet_options_stats_menu', array($this, 'show_stats_menu'));
		}
	}

	function phpmailer_init_smtp (PHPMailer $phpmailer)
	{
		if (!get_option('mailjet_enabled') || 0 == get_option('mailjet_enabled'))
			return;

		$phpmailer->Mailer = 'smtp';
		$phpmailer->SMTPSecure = get_option('mailjet_ssl');

		$phpmailer->Host = MJ_HOST;
		$phpmailer->Port = get_option('mailjet_port');

		$phpmailer->SMTPAuth = TRUE;
		$phpmailer->Username = get_option('mailjet_username');
		$phpmailer->Password = get_option('mailjet_password');

		$from_email = (get_option('mailjet_from_email') ? get_option('mailjet_from_email') : get_option('admin_email'));
		$phpmailer->From = $from_email;
		$phpmailer->Sender = $from_email;
		$phpmailer->AddCustomHeader(MJ_MAILER);
	}

	private function _get_auth_token()
	{
		$MailjetApiObject = new Mailjet(get_option('mailjet_username'), get_option('mailjet_password'));
		
		$params = array(
			'method' => 'GET',
			'APIKey' => get_option('mailjet_username'), // Use any API Key from your Sub-accounts
		);
		 
		$api_key_response = $MailjetApiObject->apikey($params);
		$result = $api_key_response->Data[0]->ID;
		 
		$params = array(
			'AllowedAccess' =>  'campaigns,contacts,reports,stats,preferences,pricing,account',
			'method' => 'POST',
			'APIKeyID' => $result,
			'TokenType' => 'url',
			'CatchedIp'  => $_SERVER['REMOTE_ADDR'],
			'log_once' => true
		);
		 
		$response = $MailjetApiObject->apitoken($params);
	 
		if(isset($response->Data) && count($response->Data) > 0)
			return $response->Data[0]->Token;
		
		return false;
	}

	public function show_campaigns_menu()
	{
		echo '<div class="wrap"><div class="icon32"><img src="' . plugin_dir_url(__FILE__) . '/images/mj_logo_med.png' . '" /></div><h2>';
		echo __('Campaigns', 'wp-mailjet');
		echo'</h2></div>';
		echo '<iframe width="980px" height="1200" src="https://app.mailjet.com/campaigns?t=' . $this->_get_auth_token() . '&show_menu=none"></iframe>';
	}

	public function show_stats_menu()
	{
		echo '<div class="wrap"><div class="icon32"><img src="' . plugin_dir_url(__FILE__) . '/images/mj_logo_med.png' . '" /></div><h2>';
		echo __('Statistics', 'wp-mailjet');
		echo'</h2></div>';
		echo '<iframe width="980px" height="1200" src="https://app.mailjet.com/stats?t=' . $this->_get_auth_token() . '&show_menu=none"></iframe>';
	}

	public function show_contacts_menu()
	{
		echo '<div class="wrap"><div class="icon32"><img src="' . plugin_dir_url(__FILE__) . '/images/mj_logo_med.png' . '" /></div><h2>';
		echo __('Contacts', 'wp-mailjet');
		echo'</h2></div>';
		echo '<iframe width="980px" height="1200" src="https://app.mailjet.com/contacts/lists?t=' . $this->_get_auth_token() . '&show_menu=none"></iframe>';
	}
}