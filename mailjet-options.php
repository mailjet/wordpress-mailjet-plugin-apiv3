<?php

/**
 * Mailjet Wordpress Plugin
 *
 * @author        Mailjet
 * @link        http://www.mailjet.com/
 *
 */
class WP_Mailjet_Options
{
    protected $api;

    public function __construct()
    {
        // Set Plugin Path
        $this->pluginPath = dirname(__FILE__);

        // Set Plugin URL
        $chunks = explode((string)DIRECTORY_SEPARATOR, dirname(__FILE__));
        $this->pluginUrl = WP_PLUGIN_URL . '/' . end($chunks);

        add_action('admin_menu', array($this, 'display_menu'));
    }

    /**
     * Display the Mailjet's plugin menu
     *
     * @param void
     * @return void
     */
    public function display_menu()
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
            add_menu_page(
                __('Manage your mailjet lists and settings', 'wp-mailjet'),
                'Mailjet',
                'read',
                'wp_mailjet_options_top_menu',
                array($this, 'show_settings_menu'),
                plugin_dir_url(__FILE__) . '/assets/images/mj_logo_small.png'
            );

            if (function_exists('add_submenu_page'))
                add_submenu_page('wp_mailjet_options_top_menu', __('Change your mailjet settings', 'wp-mailjet'), __('Settings', 'wp-mailjet'), 'read', 'wp_mailjet_options_top_menu', array($this, 'show_settings_menu'));
        }
    }

    /**
     * Define the content of the Settings page
     *
     * @param void
     * @return void
     */
    public function show_settings_menu()
    {
        if (!empty($_POST)) {
            $this->save_settings();
        }

        echo '<div class="wrap"><div class="icon32"><img src="' . plugin_dir_url(__FILE__) . '/assets/images/mj_logo_med.png" /></div><h2>';
        echo __('Mailjet Settings', 'wp-mailjet');
        echo '</h2>';
        echo '<div class="postbox-container updated" style="width:25%;float:right">
		<h3>' . __('Share the love!', 'wp-mailjet') . '</h3>
		<div style="margin-bottom:10px">
		<iframe src="//www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2FMailjet&amp;send=false&amp;layout=button_count&amp;width=150&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;height=21&amp;appId=352489811497917" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:150px; height:21px;" allowTransparency="true"></iframe>
		</div>
		<div style="margin-bottom:10px">
		<a href="https://twitter.com/share" class="twitter-share-button" data-url="http://www.mailjet.com" data-text="' . __('Improve your email deliverability and monitor action in real time.', 'wp-mailjet') . '" data-via="mailjet">' . __('Tweet', 'wp-mailjet') . '</a><script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
		</div>
		</div>
		<div style="width:70%;float:left;">';

        $form = new WP_Mailjet_Options_Form('admin.php?page=wp_mailjet_options_top_menu&action=save_options');

        $desc = '<ol>';
        $desc .= '<li>' . sprintf(__('<a target="_blank" href="https://www.mailjet.com/signup?aff=%s">Create your Mailjet account</a> if you don\'t have any.', 'wp-mailjet'), 'wordpress-3.0') . '</li>';
        $desc .= '<li>' . __('Log in with your account through the login form below or visit your <a target="_blank" href="https://www.mailjet.com/account/api_keys">account page</a> to get your API keys and set up them below.', 'wp-mailjet') . '</li>';
        $desc .= '<li>' . __('<a href="admin.php?page=wp_mailjet_options_contacts_menu">Create a new list</a> if you don\'t have one or need a new one.', 'wp-mailjet') . '</li>';
        $desc .= '<li>' . __('<a href="widgets.php">Add</a> the email collection widget to your sidebar or footer.', 'wp-mailjet') . '</li>';
        $desc .= '<li>' . __('<a href="admin.php?page=wp_mailjet_options_campaigns_menu">Create a campaign</a> on mailjet.com to send your newsletter.', 'wp-mailjet') . '</li>';
        $desc .= '<li>' . __('Should you have any questions or encounter any difficulties, please consult our <a target="_blank" href="https://www.mailjet.com/guides/wordpress-user-guide/">User Guide</a> or contact our <a target="_blank" href="https://www.mailjet.com/support/ticket">technical Support Team</a>', 'wp-mailjet') . '</li>';
        $desc .= '</ol>';

        $generalFieldset = new WP_Mailjet_Options_Form_Fieldset(
            __('Mailjet Plugin', 'wp-mailjet'),
            array(),
            $desc,
            true
        );

        $form->addFieldset($generalFieldset);

        /* Api field set */
        $apiOptions[] = new WP_Mailjet_Options_Form_Option('mailjet_username', __('API key', 'wp-mailjet'), 'text', get_option('mailjet_username'), null, TRUE);
        $apiOptions[] = new WP_Mailjet_Options_Form_Option('mailjet_password', __('Secret key', 'wp-mailjet'), 'text', get_option('mailjet_password'), null, TRUE);

        $apiFieldset = new WP_Mailjet_Options_Form_Fieldset(
            __('API Settings', 'wp-mailjet'),
            $apiOptions,
            sprintf(__('You can get your API keys from <a target="_blank" href="https://www.mailjet.com/account/api_keys">your mailjet account</a>. Please also make sure the sender address %s is active in <a target="_blank" href="https://www.mailjet.com/account/sender">your account</a>', 'wp-mailjet'), get_option('admin_email')),
            true
        );

        $form->addFieldset($apiFieldset);
        /* END - Api field set */


        /* General field set */
        $generalOptions[] = new WP_Mailjet_Options_Form_Option('mailjet_enabled', ' ' . __('Enabled', 'wp-mailjet'),
            'checkbox', get_option('mailjet_enabled'), __('Enable email through <b>Mailjet</b>', 'wp-mailjet'));

        $generalOptions[] = new WP_Mailjet_Options_Form_Option(
            'mailjet_ssl',
            ' ' . __('SSL Enabled (only with port 465)', 'wp-mailjet'),
            'checkbox', get_option('mailjet_ssl'),
            __('Enable <b>SSL</b> communication with mailjet.com', 'wp-mailjet')
        );

        $ports = array(
            array('value' => 25, 'label' => 25),
            array('value' => 465, 'label' => 465),
            array('value' => 587, 'label' => 587),
            array('value' => 588, 'label' => 588),
            array('value' => 80, 'label' => 80),
        );

        $generalOptions[] = new WP_Mailjet_Options_Form_Option('mailjet_port', '', 'select', get_option('mailjet_port'),
            __('Port to use for SMTP communication', 'wp-mailjet'), FALSE, $ports);

        $generalOptions[] = new WP_Mailjet_Options_Form_Option('mailjet_test', ' ' . __('Send test email', 'wp-mailjet'),
            'checkbox', get_option('mailjet_test'), __('Send test email now', 'wp-mailjet'));

        $test_email = (get_option('mailjet_test_address') ? get_option('mailjet_test_address') : get_option('admin_email'));

        $generalOptions[] = new WP_Mailjet_Options_Form_Option('mailjet_test_address',
            __('Recipient of test email', 'wp-mailjet'), 'email', $test_email);

        $from_email = (get_option('mailjet_from_email') ? get_option('mailjet_from_email') : get_option('admin_email'));

        $generalOptions[] = new WP_Mailjet_Options_Form_Option('mailjet_from_email', __('<code>From:</code> email address',
            'wp-mailjet'), 'email', $from_email);

        $from_name = (get_option('mailjet_from_name') ? get_option('mailjet_from_name') : '');

        $generalOptions[] = new WP_Mailjet_Options_Form_Option('mailjet_from_name', __('<code>From:</code> Name', 'wp-mailjet'), 'text', $from_name, null, TRUE);

// General settings
        $generalFieldset = new WP_Mailjet_Options_Form_Fieldset(
            __('General Settings', 'wp-mailjet'),
            $generalOptions,
            __('Enable or disable the sending of your emails through your Mailjet account', 'wp-mailjet'),
            true
        );

        $form->addFieldset($generalFieldset);
        /* END - General field set */


        if (get_option('mailjet_password') && get_option('mailjet_username')) {
            // Get the list of contact lists and order them in a properly set list
            $this->api = new WP_Mailjet_Api(get_option('mailjet_username'), get_option('mailjet_password'));
            $resp = $this->api->getContactLists(array('limit' => 0));

// Initial Sync
            // get the name of the preselected contact list
            $lastSyncMessage = __('Initial sync not yet executed, choose a list and save settings to start it', 'wp-mailjet');
            $lastSyncedContactListName = '';
            $lastSyncedDate = '';
            $initialSyncStatus = 'Inactive';
            if (is_array($resp)) {
                foreach ($resp as $contactList) {
                    if ($contactList['value'] == get_option('mailjet_initial_sync_last_list_id')) {
                        $lastSyncedContactListName = $contactList['label'];
                    }
                }
                if (get_option('mailjet_initial_sync_last_date')) {
                    $lastSyncedDate = get_option('mailjet_initial_sync_last_date');
                }
                if (get_option('mailjet_initial_sync_list_id')) {
                    $initialSyncStatus = 'Active';
                }

                if (!empty($lastSyncedContactListName) && !empty($lastSyncedDate) && !empty($initialSyncStatus)) {
                    $lastSyncMessage = sprintf(__('Initial users sync is <b>%s</b>! Existing contacts subscribed to <b>%s</b> on  <b>%s</b>', 'wp-mailjet'), $initialSyncStatus, $lastSyncedContactListName, $lastSyncedDate);
                }
            }

            $desc = '<ul>';
            $desc .= '<li>' . sprintf(__('Choose a Mailjet contact list which you would like to subscribe your Wordpress users to.', 'wp-mailjet')) . '</li>';
            $desc .= '<li>' . $lastSyncMessage . '</li>';
            $desc .= '</ul>';

            $lists = array(array('value' => '', 'label' => __('Disable initial sync', 'wp-mailjet')));
            if (!(isset($resp->Status) && $resp->Status == 'ERROR') && count($resp) > 0) {
                usort($resp, array($this, 'sortByLabel'));
                $lists = array_merge($lists, $resp);
            }

            $syncOptions[] = new WP_Mailjet_Options_Form_Option('mailjet_initial_sync_list_id', '', 'select',
                get_option('mailjet_initial_sync_list_id'), __('Subscribe existing users to this list', 'wp-mailjet'), FALSE, $lists);

// Auto subscribe
            $lists = array(array('value' => '', 'label' => __('Disable autosubscribe', 'wp-mailjet')));
            if (!(isset($resp->Status) && $resp->Status == 'ERROR') && count($resp) > 0) {
                usort($resp, array($this, 'sortByLabel'));
                $lists = array_merge($lists, $resp);
            }

            $syncOptions[] = new WP_Mailjet_Options_Form_Option('mailjet_auto_subscribe_list_id', '', 'select',
                get_option('mailjet_auto_subscribe_list_id'), __('Auto subscribe new users to this list', 'wp-mailjet'), FALSE, $lists);


            $syncFieldset = new WP_Mailjet_Options_Form_Fieldset(
                __('Subscribe Wordpress users', 'wp-mailjet'),
                $syncOptions,
                $desc,
                true
            );

            $form->addFieldset($syncFieldset);


// Comment Authors


            // get the name of the preselected contact list
            $lastSyncMessage = __('Comment author sync not yet activated, choose a list and save settings to activate it', 'wp-mailjet');
            $lastSyncedContactListName = '';
            $lastSyncedDate = '';
            $commentAuthorsSyncStatus = 'Inactive';
            if (is_array($resp)) {
                foreach ($resp as $contactList) {
                    if ($contactList['value'] == get_option('mailjet_comment_authors_last_list_id')) {
                        $lastSyncedContactListName = $contactList['label'];
                    }
                }
                if (get_option('mailjet_comment_authors_list_date')) {
                    $lastSyncedDate = get_option('mailjet_comment_authors_list_date');
                }
                if (get_option('mailjet_comment_authors_list_id')) {
                    $commentAuthorsSyncStatus = 'Active';
                }
                if (!empty($lastSyncedContactListName) && !empty($lastSyncedDate) && !empty($commentAuthorsSyncStatus)) {
                    $lastSyncMessage = sprintf(__('Comment authors sync is <b>%s</b>!', 'wp-mailjet'), $commentAuthorsSyncStatus);
                }
            }


            $desc = '<ul>';
            $desc .= '<li>' . sprintf(__('This feature adds a "Subscribe to our mailing list" checkbox in the "Leave a reply" form, so that comment authors can automatically join a contact list of your choice.', 'wp-mailjet')) . '</li>';
            $desc .= '<li>' . $lastSyncMessage . '</li>';
            $desc .= '</ul>';

            $lists = array(array('value' => '', 'label' => __('Disable comment authors subscription', 'wp-mailjet')));
            if (!(isset($resp->Status) && $resp->Status == 'ERROR') && count($resp) > 0) {
                usort($resp, array($this, 'sortByLabel'));
                $lists = array_merge($lists, $resp);
            }

            $commentAuthorsOptions[] = new WP_Mailjet_Options_Form_Option('mailjet_comment_authors_list_id', '', 'select',
                get_option('mailjet_comment_authors_list_id'), __('Allow comment authors to subscribe to this list', 'wp-mailjet'), FALSE, $lists);

            $commentAuthorsFieldset = new WP_Mailjet_Options_Form_Fieldset(
                __('Subscribe comment authors', 'wp-mailjet'),
                $commentAuthorsOptions,
                $desc,
                true
            );

            $form->addFieldset($commentAuthorsFieldset);
        }


        /* Add access field set */
        if (current_user_can('administrator')) {
            $accessOptions[] = new WP_Mailjet_Options_Form_Option('mailjet_access_administrator', ' ' . __('Administrator', 'wp-mailjet'), 'checkbox', TRUE, __('User roles able to access the plugin', 'wp-mailjet'), TRUE, null, TRUE);
            $accessOptions[] = new WP_Mailjet_Options_Form_Option('mailjet_access_editor', ' ' . __('Editor', 'wp-mailjet'), 'checkbox', get_option('mailjet_access_editor'), '');
            $accessOptions[] = new WP_Mailjet_Options_Form_Option('mailjet_access_author', ' ' . __('Author', 'wp-mailjet'), 'checkbox', get_option('mailjet_access_author'), '');
            $accessOptions[] = new WP_Mailjet_Options_Form_Option('mailjet_access_contributor', ' ' . __('Contributor', 'wp-mailjet'), 'checkbox', get_option('mailjet_access_contributor'), '');
            $accessOptions[] = new WP_Mailjet_Options_Form_Option('mailjet_access_subscriber', ' ' . __('Subscriber', 'wp-mailjet'), 'checkbox', get_option('mailjet_access_subscriber'), '');

            $accessFieldset = new WP_Mailjet_Options_Form_Fieldset(
                __('Access Settings', 'wp-mailjet'),
                $accessOptions,
                __('Select which WordPress admin user roles (in addition to Administrator) will also have access to the Mailjet Plug-in', 'wp-mailjet'),
                true
            );

            $form->addFieldset($accessFieldset);
        }
        /* END - Add access field set */

        $form->display();
        echo '</div></div>';
    }

    private function sortByLabel($a, $b)
    {
        $a = $a['label'];
        $b = $b['label'];

        if ($a == $b) return 0;
        return ($a < $b) ? -1 : 1;
    }

    public function hasValidSender($from_email, $senders)
    {
        if (!empty($senders->Status) && $senders->Status === 'ERROR') {
            return false;
        }
        $domainArr = explode('@', $from_email);
        $domainName = array_pop($domainArr);
        return (in_array($from_email, $senders['email']) || in_array($domainName, $senders['domain']));
    }

    /**
     * Save the Mailjet's plugin settings when we click "Save options" button
     *
     * @param void
     * @return void
     */
    public function save_settings()
    {
        // Get the variables which we'll save
        $fields['mailjet_enabled'] = (isset($_POST['mailjet_enabled']) ? 1 : 0);
        $fields['mailjet_test'] = (isset($_POST['mailjet_test']) ? 1 : 0);
        $fields['mailjet_ssl'] = (isset($_POST['mailjet_ssl']) ? 'ssl' : '');
        $fields['mailjet_test_address'] = strip_tags(filter_var($_POST['mailjet_test_address'], FILTER_VALIDATE_EMAIL));
        $fields['mailjet_from_email'] = strip_tags(filter_var($_POST['mailjet_from_email'], FILTER_VALIDATE_EMAIL));
        $fields['mailjet_from_name'] = strip_tags(filter_var($_POST['mailjet_from_name'], FILTER_SANITIZE_STRING));
        $fields['mailjet_username'] = trim(strip_tags(filter_var($_POST['mailjet_username'], FILTER_SANITIZE_STRING)));
        $fields['mailjet_password'] = trim(strip_tags(filter_var($_POST['mailjet_password'], FILTER_SANITIZE_STRING)));
        $fields['mailjet_port'] = strip_tags(filter_var($_POST['mailjet_port'], FILTER_SANITIZE_NUMBER_INT));
        $fields['mailjet_initial_sync_list_id'] = ($fields['mailjet_username'] != get_option('mailjet_username') || $fields['mailjet_password'] != get_option('mailjet_password'))
            ? false
            : strip_tags(filter_var($_POST['mailjet_initial_sync_list_id'], FILTER_SANITIZE_NUMBER_INT));
        $fields['mailjet_comment_authors_list_id'] = ($fields['mailjet_username'] != get_option('mailjet_username') || $fields['mailjet_password'] != get_option('mailjet_password'))
            ? false
            : strip_tags(filter_var($_POST['mailjet_comment_authors_list_id'], FILTER_SANITIZE_NUMBER_INT));
        $fields['mailjet_auto_subscribe_list_id'] = ($fields['mailjet_username'] != get_option('mailjet_username') || $fields['mailjet_password'] != get_option('mailjet_password'))
            ? false
            : strip_tags(filter_var($_POST['mailjet_auto_subscribe_list_id'], FILTER_SANITIZE_NUMBER_INT));
        if (current_user_can('administrator')) {
            $fields['mailjet_access_editor'] = (isset($_POST['mailjet_access_editor']) ? 1 : 0);
            $fields['mailjet_access_author'] = (isset($_POST['mailjet_access_author']) ? 1 : 0);
            $fields['mailjet_access_contributor'] = (isset($_POST['mailjet_access_contributor']) ? 1 : 0);
            $fields['mailjet_access_subscriber'] = (isset($_POST['mailjet_access_subscriber']) ? 1 : 0);
        }

        // Set error messages if we've any
        $errors = array();
        if ($fields['mailjet_test'] && empty($fields['mailjet_test_address'])) {
            $errors[] = 'mailjet_test_address';
        }

        if (!empty($fields['mailjet_test_address'])) {
            if (!filter_var($fields['mailjet_test_address'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'mailjet_test_address';
            }
        }

        if (empty($fields['mailjet_username'])) {
            $errors[] = 'mailjet_username';
        }

        if (empty($fields['mailjet_password'])) {
            $errors[] = 'mailjet_password';
        }

        // If there are no errors, then update the new settings
        if (!count($errors)) {
            if ($fields['mailjet_ssl'] == 'ssl') {
                $fields['mailjet_port'] = 465;
            }

            // Update the new settings
            update_option('mailjet_enabled', $fields['mailjet_enabled']);
            update_option('mailjet_token' . $_SERVER['REMOTE_ADDR'], json_encode(array('timestamp' => 0)));
            update_option('mailjet_test', $fields['mailjet_test']);
            update_option('mailjet_test_address', $fields['mailjet_test_address']);
            update_option('mailjet_from_email', $fields['mailjet_from_email']);
            update_option('mailjet_from_name', $fields['mailjet_from_name']);
            update_option('mailjet_ssl', $fields['mailjet_ssl']);
            update_option('mailjet_port', $fields['mailjet_port']);
            if (!empty($fields['mailjet_initial_sync_list_id'])) {
                update_option('mailjet_initial_sync_last_list_id', $fields['mailjet_initial_sync_list_id']);
                update_option('mailjet_initial_sync_last_date', current_time("Y-m-d H:i:s "));
            }
            update_option('mailjet_initial_sync_list_id', $fields['mailjet_initial_sync_list_id']);
            if (!empty($fields['mailjet_comment_authors_list_id'])) {
                update_option('mailjet_comment_authors_last_list_id', $fields['mailjet_comment_authors_list_id']);
                update_option('mailjet_comment_authors_list_date', current_time("Y-m-d H:i:s "));
            }
            update_option('mailjet_comment_authors_list_id', $fields['mailjet_comment_authors_list_id']);
            update_option('mailjet_auto_subscribe_list_id', $fields['mailjet_auto_subscribe_list_id']);
            if (current_user_can('administrator')) {
                update_option('mailjet_access_editor', $fields['mailjet_access_editor']);
                update_option('mailjet_access_author', $fields['mailjet_access_author']);
                update_option('mailjet_access_contributor', $fields['mailjet_access_contributor']);
                update_option('mailjet_access_subscriber', $fields['mailjet_access_subscriber']);
            }

            // Establish API connection because we will need it to check if the API and secrect keys are correct
            $this->api = new WP_Mailjet_Api($fields['mailjet_username'], $fields['mailjet_password']);
            update_option('mailjet_user_api_version',
                $this->api->findUserApiVersion($fields['mailjet_username'], $fields['mailjet_password']));
            // get proper instance of the API class, after user API DB version update
            $this->api = new WP_Mailjet_Api($fields['mailjet_username'], $fields['mailjet_password']);

            // Check if there is a connection with the Mailjet's server
            $configs = array(
                array('', 25),
                array('tls', 25),
                array('ssl', 465),
                array('tls', 587),
                array('', 587),
                array('', 588),
                array('', 80),
            );

            $connected = FALSE;
            $protocol = '';
            if (get_option('mailjet_ssl')) {
                $protocol = 'ssl://';
            }

            $soc = @fsockopen($protocol . $this->api->mj_host, get_option('mailjet_port'), $errno, $errstr, 5);

            if ($soc) {
                $connected = TRUE;
                $port = get_option('mailjet_port');
                $ssl = get_option('mailjet_ssl');
            } else {
                for ($i = 0; $i < count($configs); ++$i) {
                    if ($configs[$i][0])
                        $protocol = $configs[$i][0] . '://';
                    else
                        $protocol = '';

                    $soc = @fsockopen($protocol . $this->api->mj_host, $configs[$i][1], $errno, $errstr, 5);
                    if ($soc) {
                        fclose($soc);
                        $connected = $i;
                        $port = $configs[$i][1];
                        $ssl = $configs[$i][0];
                        break;
                    }
                }
            }

            // Get all senders
            $senders = $this->api->getSenders(array('limit' => 0));
            $from_email = $fields['mailjet_from_email'] ? $fields['mailjet_from_email'] : get_option('admin_email');

            // If there is connection, display successful message
            if ($connected !== FALSE) {

                if(!$this->hasValidSender($from_email, $senders)) {
                    WP_Mailjet_Utils::custom_notice('error', __('Please make sure that you are using the correct API key and secret key associated to your mailjet account (from email).', 'wp-mailjet'));
                }
                // Record API and secret keys in WP DB only on successful connect
                update_option('mailjet_ssl', $ssl);
                update_option('mailjet_port', $port);
                update_option('mailjet_username', $fields['mailjet_username']);
                update_option('mailjet_password', $fields['mailjet_password']);

                $test_sent = FALSE;

                if (!empty($fields['mailjet_test_address']) &&
                    !empty($fields['mailjet_test']) &&
                    $this->hasValidSender($from_email, $senders)
                ) {
                    // Send a test mail
                    $subject = __('Your test mail from Mailjet', 'wp-mailjet');
                    $message = sprintf(__('Your Mailjet configuration is ok!' . 'SSL: %s Port: %s', 'wp-mailjet'), ($ssl ? 'On' : 'Off'), $port);
                    $enabled = get_option('mailjet_enabled');
                    update_option('mailjet_enabled', 1);
                    $test_sent = wp_mail($fields['mailjet_test_address'], $subject, $message);
                    update_option('mailjet_enabled', $enabled);
                }

                $sent = '';
                if ($test_sent) {
                    $sent = __(' and your test message was sent.', 'wp-mailjet');
                }

                if ($connected === TRUE) {
                    $domainsArray = explode('@', $from_email);
                    $domainName = array_pop($domainsArray);
                    if (!in_array($from_email, $senders['email']) && !in_array($domainName, $senders['domain']))
                        WP_Mailjet_Utils::custom_notice('updated', __('Your settings have been saved successfully', 'wp-mailjet') . '.');
                    else
                        WP_Mailjet_Utils::custom_notice('updated', __('Your settings have been saved successfully', 'wp-mailjet') . $sent);
                } elseif ($connected >= 0) {
                    WP_Mailjet_Utils::custom_notice('updated', __('Your settings have been saved, but your port and SSL settings were changed as follows to ensure delivery', 'wp-mailjet') . $sent);
                }

                if (intval(get_option('mailjet_initial_sync_list_id')) > 0) {
                    $this->syncAllWpUsers();
                }

            } else {
                // Error message
                $link = 'https://www.mailjet.com/account/api_keys';
                WP_Mailjet_Utils::custom_notice('error', sprintf(__('Please verify that you have entered your API and secret key correctly. If this is the case and you have still this error message, please go to Account API keys (<a href="%s" target="_blank">%s</a>) to regenerate a new Secret Key for the plug-in.', 'wp-mailjet'), $link, $link));
            }
        } else {
            // Error message
            WP_Mailjet_Utils::custom_notice('error', __('There is an error with your settings. please correct and try again', 'wp-mailjet'));
        }
    }


    public function syncAllWpUsers()
    {
        $this->api->createMetaContactProperty(array(
            'name' => 'first_name',
            'dataType' => 'str'
        ));
        $this->api->createMetaContactProperty(array(
            'name' => 'last_name',
            'dataType' => 'str'
        ));
        $resp = $this->api->createMetaContactProperty(array(
            'name' => 'wp_user_role',
            'dataType' => 'str'
        ));

        $contacts = array();
        $users = get_users(array('fields' => array('ID', 'user_email')));

        if ($users) {
            foreach ($users as $user) {
                $userInfo = get_userdata($user->ID);
                $userRoles = $userInfo->roles;
                $userMetadata = get_user_meta($user->ID);

                $contactProperties = array();
                if (!empty($userMetadata['first_name'][0])) {
                    $contactProperties['first_name'] = $userMetadata['first_name'][0];
                }
                if (!empty($userMetadata['last_name'][0])) {
                    $contactProperties['last_name'] = $userMetadata['last_name'][0];
                }
                if (!empty($userRoles[0])) {
                    $contactProperties['wp_user_role'] = $userRoles[0];
                }

                $contacts[] = array(
                    'Email' => $user->user_email,
                    'Properties' => $contactProperties
                );
            }
        }

        $this->asyncManageContactsToList($contacts, get_option('mailjet_initial_sync_list_id'), 'addnoforce');
    }




    public function asyncManageContactsToList($contacts, $list_id, $action = 'addnoforce')
    {
        $params = array(
            "listId" => $list_id,
            "action" => $action,
            "contacts" => $contacts
        );

        $asyncJobResponse = $this->api->manageManyContacts($params);

        return $asyncJobResponse;

    }
}
