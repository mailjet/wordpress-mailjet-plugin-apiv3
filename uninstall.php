<?php

if (!defined('ABSPATH') || !defined('WP_UNINSTALL_PLUGIN')) {
    exit();  // silence is golden
}

foreach(array(
            'mailjet_username',
            'mailjet_password',
            'widget_wp_mailjet_subscribe_widget',
            'mailjet_access_administrator',
            'mailjet_access_editor',
            'mailjet_access_author',
            'mailjet_access_contributor',
            'mailjet_access_subscriber',
            'mailjet_enabled',
            'mailjet_ssl',
            'mailjet_port',
            'mailjet_from_email',
            'mailjet_test',
            'mailjet_test_address',
            'mailjet_auto_subscribe_list_id',
            'mailjet_user_api_version'
        ) as $option) {
    delete_option($option);
}

?>
