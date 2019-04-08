<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MailjetPlugin\Includes\SettingsPages;

use MailjetPlugin\Includes\SettingsPages\SubscriptionOptionsSettings;
/**
 * Description of ContactFormSettings
 *
 * @author g.nikolov
 */
class ContactFormSettings
{

    const MAILJET_CHECKBOX = 'mailjet-opt-in';

    public function sendConfirmationEmail($contactForm)
    {
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

        $mailjetCheckbox = $formdata[self::MAILJET_CHECKBOX];
        if ($mailjetCheckbox != '') {
            $cf7_email = trim(get_option('cf7_email'), '[]');
            $email = $formdata[$cf7_email];

            $cf7name = get_option('cf7_fromname');
            $matches = array();
            $data = array();
            preg_match_all('/\[(.*?)\]/', $cf7name, $matches);

            if (!$matches[0] && !$matches[1]) {
                return false;
            }

            foreach($matches[1] as $match) {
                $data[] = $formdata[$match];
            }

            $newphrase = str_replace($matches[0], $data, $cf7name);
            $mailjetCF7List = get_option('mailjet_cf7_list');

            $params = http_build_query(array(
                'cf7list' => $mailjetCF7List,
                'email' => $email,
                'prop' => $newphrase,
            ));
            $wpUrl = sprintf('<a href="%s" target="_blank">%s</a>', get_home_url(), get_home_url());
            $message = file_get_contents(dirname(dirname(dirname(__FILE__))) . '/templates/confirm-subscription-email.php');
            $emailParams = array(
                '__EMAIL_TITLE__' => __('Please confirm your subscription', 'mailjet-for-wordpress'),
                '__EMAIL_HEADER__' => sprintf(__('To receive newsletters from %s please confirm your subscription by clicking the following button:', 'mailjet-for-wordpress'), $wpUrl),
                '__WP_URL__' => $wpUrl,
                '__CONFIRM_URL__' => get_home_url() . '?'.$params.'&token=' . sha1($params),
                '__CLICK_HERE__' => __('Yes, subscribe me to this list', 'mailjet-for-wordpress'),
                '__FROM_NAME__' => get_option('blogname'),
                '__IGNORE__' => __('If you received this email by mistake or don\'t wish to subscribe anymore, simply ignore this message.', 'mailjet-for-wordpress'),
            );
            foreach ($emailParams as $key => $value) {
                $message = str_replace($key, $value, $message);
            }

            $email_subject = __('Subscription Confirmation', 'mailjet');
            add_filter('wp_mail_content_type', array(new SubscriptionOptionsSettings(), 'set_html_content_type'));
            wp_mail($email, $email_subject, $message, array('From: ' . get_option('blogname') . ' <' . get_option('admin_email') . '>'));
        }

        return false;
    }

}
