<?php

namespace MailjetPlugin\Includes\SettingsPages;

use MailjetPlugin\Includes\Mailjeti18n;
use MailjetPlugin\Includes\MailjetSettings;

/**
 * Description of ContactForm7Settings
 *
 * @author g.nikolov
 */
class ContactForm7Settings
{

    const MAILJET_CHECKBOX = 'mailjet-opt-in';

    public function sendConfirmationEmail($contactForm)
    {
        $locale = Mailjeti18n::getLocale();
        $formdata = false;

        if (isset($contactForm->posted_data)) {
            $formdata = $contactForm->posted_data;
        } else {
            $submission = \WPCF7_Submission::get_instance();
            if ($submission) {
                $formdata = $submission->get_posted_data();
            }
        }

        if (!$formdata) {
            return false;
        }

        $invalidFields = $submission->get_invalid_fields();
        if (!empty($invalidFields)) {
            return false;
        }

        $mailjetCheckbox = $formdata[self::MAILJET_CHECKBOX];
        if ($mailjetCheckbox[0] != '') {
            $cf7Email = trim(stripslashes(get_option('cf7_email')), '[]');
            if (!isset($formdata[$cf7Email])) {
                return false;
            }
            $email = $formdata[$cf7Email];

            $cf7name = stripslashes(get_option('cf7_fromname', ''));
            $matches = array();
            $data = array();
            preg_match_all('/\[(.*?)\]/', $cf7name, $matches);

            if (!empty($matches[1])) {
                foreach ($matches[1] as $match) {
                    if (isset($formdata[$match])) {
                        $data[] = $formdata[$match];
                    }
                }
            }

            $newphrase = str_replace($matches[0], $data, $cf7name);
            $mailjetCF7List = get_option('mailjet_cf7_list');

            $params = http_build_query(array(
                'cf7list' => $mailjetCF7List,
                'email' => $email,
                'prop' => $newphrase,
            ));
            $wpUrl = sprintf('<a href="%s" target="_blank">%s</a>', get_home_url(), get_home_url());
            $subscriptionTemplate = apply_filters('mailjet_confirmation_email_filename', dirname(dirname(dirname(__FILE__))) . '/templates/confirm-subscription-email.php');
            $message = file_get_contents($subscriptionTemplate);

            $emailParams = array(
                '__EMAIL_TITLE__' =>  Mailjeti18n::getTranslationsFromFile($locale, 'Please confirm your subscription'),
                '__EMAIL_HEADER__' => sprintf(__( Mailjeti18n::getTranslationsFromFile($locale, 'To receive newsletters from %s please confirm your subscription by clicking the following button:'), 'mailjet-for-wordpress'), $wpUrl),
                '__WP_URL__' => $wpUrl,
                '__CONFIRM_URL__' => get_home_url() . '?' . $params . '&token=' . sha1($params . MailjetSettings::getCryptoHash()),
                '__CLICK_HERE__' => Mailjeti18n::getTranslationsFromFile($locale, 'Yes, subscribe me to this list'),
                '__FROM_NAME__' => get_option('blogname'),
                '__IGNORE__' => Mailjeti18n::getTranslationsFromFile($locale, 'If you received this email by mistake or don\'t wish to subscribe anymore, simply ignore this message.'),
            );
            foreach ($emailParams as $key => $value) {
                $message = str_replace($key, $value, $message);
            }

            $email_subject =  Mailjeti18n::getTranslationsFromFile($locale, 'Subscription Confirmation');
            add_filter('wp_mail_content_type', array(SubscriptionOptionsSettings::getInstance(), 'set_html_content_type'));
            wp_mail($email, $email_subject, $message, array('From: ' . get_option('blogname') . ' <' . get_option('admin_email') . '>'));
        }

        return false;
    }

}
