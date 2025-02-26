<?php

namespace MailjetWp\MailjetPlugin\Includes;

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    Mailjet
 * @subpackage Mailjet/includes
 * @author     Your Name <email@example.com>
 */
class MailjetMail {

    public const MJ_HOST   = 'in-v3.mailjet.com';
    public const MJ_MAILER = 'X-Mailer:WP-Mailjet/0.1';
    public function __construct() {
        if (\version_compare(get_bloginfo('version'), '5.5-alpha', '<')) {
            require_once ABSPATH . WPINC . '/class-phpmailer.php';
            require_once ABSPATH . WPINC . '/class-smtp.php';
        } else {
            require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
            require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
        }
    }
    public function phpmailer_init_smtp( $phpmailer ) {
        if ( ! Mailjet::getOption('mailjet_enabled') || 0 === (int) Mailjet::getOption('mailjet_enabled')) {
            return;
        }
        $phpmailer->Mailer     = 'smtp';
        $phpmailer->SMTPSecure = Mailjet::getOption('mailjet_ssl');
        $phpmailer->Host       = self::MJ_HOST;
        $phpmailer->Port       = Mailjet::getOption('mailjet_port');
        $phpmailer->SMTPAuth   = \TRUE;
        $phpmailer->Username   = Mailjet::getOption('mailjet_apikey');
        $phpmailer->Password   = Mailjet::getOption('mailjet_apisecret');
        $from_email            = Mailjet::getOption('mailjet_from_email') ? Mailjet::getOption('mailjet_from_email') : Mailjet::getOption('admin_email');
        $phpmailer->From       = $from_email;
        $phpmailer->Sender     = $from_email;
        $phpmailer->FromName   = Mailjet::getOption('mailjet_from_name') ? Mailjet::getOption('mailjet_from_name') : get_bloginfo('name');
        $phpmailer->AddCustomHeader(self::MJ_MAILER);
    }
    public function wp_mail_failed_cb( $wpError ) {
        add_action('admin_notices', array( $this, 'wp_mail_failed_admin_notice' ));
        if ( ! Mailjet::getOption('mailjet_enabled')) {
            return \false;
        }
        if (\function_exists('MailjetWp\\add_settings_error')) {
            add_settings_error('mailjet_messages', 'mailjet_message', 'ERROR - ' . $wpError->get_error_message(), 'error');
        }
    }
    public function wp_mail_failed_admin_notice() {
        global $pagenow;
        if ($pagenow === 'index.php') {
            $user = wp_get_current_user();
            if ($user->exists()) {
                echo '<div class="notice notice-error is-dismissible">' . __('Email sending failed. Please review your smtp configuration and try again later', 'mailjet-for-wordpress') . '</div>';
            }
        }
    }
    public static function sendTestEmail() {
        $testSent           = \false;
        $mailjetTestAddress = Mailjet::getOption('mailjet_test_address');
        if (empty($mailjetTestAddress)) {
            return $testSent;
        }
        // Send a test mail
        add_filter('wp_mail_content_type', array( 'MailjetWp\\MailjetPlugin\\Includes\\MailjetMail', 'set_html_content_type' ));
        $subject = __('Your test mail from Mailjet', 'mailjet-for-wordpress');
        $message = \sprintf(__('Your Mailjet configuration is ok! <br /> Site URL: %s <br /> SSL: %s <br /> Port: %s', 'mailjet-for-wordpress'), get_home_url(), Mailjet::getOption('mailjet_ssl') ? 'On' : 'Off', Mailjet::getOption('mailjet_port'));
        return wp_mail(Mailjet::getOption('mailjet_test_address'), $subject, $message);
    }
    public static function set_html_content_type() {
        return 'text/html';
    }
    public function wp_sender_email( $original_email_address ) {
        return Mailjet::getOption('mailjet_from_email');
    }
    public function wp_sender_name( $original_email_from ) {
        return Mailjet::getOption('mailjet_from_name');
    }
}
