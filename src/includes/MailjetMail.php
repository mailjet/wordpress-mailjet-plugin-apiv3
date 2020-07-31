<?php

namespace MailjetPlugin\Includes;


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
class MailjetMail
{
    const MJ_HOST = 'in-v3.mailjet.com';
    const MJ_MAILER = 'X-Mailer:WP-Mailjet/0.1';

    public function __construct()
    {
        if ( version_compare( get_bloginfo( 'version' ), '5.5-alpha', '<' ) ) {
            require_once ABSPATH . WPINC . '/class-phpmailer.php';
            require_once ABSPATH . WPINC . '/class-smtp.php';
        } else {
            require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
            require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
        }
    }

    public function phpmailer_init_smtp($phpmailer)
    {
        if (!get_option('mailjet_enabled') || 0 == get_option('mailjet_enabled')) {
            return;
        }

        $phpmailer->Mailer = 'smtp';
        $phpmailer->SMTPSecure = get_option('mailjet_ssl');

        $phpmailer->Host = self::MJ_HOST;
        $phpmailer->Port = get_option('mailjet_port');

        $phpmailer->SMTPAuth = TRUE;
        $phpmailer->Username = get_option('mailjet_apikey');
        $phpmailer->Password = get_option('mailjet_apisecret');

        $from_email = (get_option('mailjet_from_email') ? get_option('mailjet_from_email') : get_option('admin_email'));
        $phpmailer->From = $from_email;
        $phpmailer->Sender = $from_email;

        $phpmailer->FromName = get_option('mailjet_from_name') ? get_option('mailjet_from_name') : get_bloginfo('name');

        $phpmailer->AddCustomHeader(self::MJ_MAILER);
    }

    public function wp_mail_failed_cb($wpError)
    {
        add_action('admin_notices', array($this, 'wp_mail_failed_admin_notice'));
        if(!get_option('mailjet_enabled')) {
            return false;
        }
        if (function_exists('add_settings_error')) {
            add_settings_error('mailjet_messages', 'mailjet_message', 'ERROR - '. $wpError->get_error_message(), 'error');
        }
    }


    public function wp_mail_failed_admin_notice()
    {
        global $pagenow;
        if ($pagenow == 'index.php') {
            $user = wp_get_current_user();
            if ($user->exists()) {
                echo '<div class="notice notice-error is-dismissible">' . __('Email sending failed. Please review your smtp configuration and try again later', 'mailjet-for-wordpress') . '</div>';
            }
        }
    }

    public static function sendTestEmail()
    {
        $testSent = false;
        $mailjetTestAddress = get_option('mailjet_test_address');
        if (empty($mailjetTestAddress)) {
            //MailjetLogger::error('[ Mailjet ] [ ' . __METHOD__ . ' ] [ Line #' . __LINE__ . ' ] [ Missing email address to send test email to ]');
            return;
        }
        // Send a test mail
        add_filter('wp_mail_content_type', array('MailjetPlugin\Includes\MailjetMail', 'set_html_content_type'));
        $subject = __('Your test mail from Mailjet', 'mailjet-for-wordpress');
        $message = sprintf(__('Your Mailjet configuration is ok! <br /> Site URL: %s <br /> SSL: %s <br /> Port: %s', 'mailjet-for-wordpress'), get_home_url(), (get_option('mailjet_ssl') ? 'On' : 'Off'), get_option('mailjet_port'));
        $testSent = wp_mail(get_option('mailjet_test_address'), $subject, $message);
        return $testSent;
    }

    public static function set_html_content_type()
    {
        return 'text/html';
    }

    public function wp_sender_email($original_email_address) {
        return get_option('mailjet_from_email');
    }

    public function wp_sender_name($original_email_from) {
        return get_option('mailjet_from_name');
    }

}
