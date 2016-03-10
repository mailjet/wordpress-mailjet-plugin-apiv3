<?php

/**
 * Mailjet Wordpress Plugin
 *
 * @author        Mailjet
 * @link        http://www.mailjet.com/
 *
 */
class WP_Mailjet
{
    protected $api;
    protected $phpmailer;

    public $langs = array(
        'en' => array('locale' => 'en_US', 'label' => 'English'),
        'fr' => array('locale' => 'fr_FR', 'label' => 'French'),
        'de' => array('locale' => 'de_DE', 'label' => 'German'),
        'es' => array('locale' => 'es_ES', 'label' => 'Spanish'),
    );

    public function __construct($api, $phpMailer)
    {
        $this->pluginPath = dirname(__FILE__);
        $chunks = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
        $this->pluginUrl = WP_PLUGIN_URL . '/' . end($chunks);
        $this->api = $api;
        $this->phpmailer = $phpMailer;
        add_action('phpmailer_init', array($this, 'phpmailer_init_smtp'));
        add_action('admin_menu', array($this, 'display_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function enqueue_scripts()
    {
        wp_enqueue_script('jquery-ui');
        wp_enqueue_script('jquery-ui-accordion');
        wp_enqueue_script('jquery-ui-tooltip');
        wp_enqueue_style('mailjet-jquery-ui', plugins_url('/assets/mailjet-jquery-ui.css', __FILE__));
        wp_enqueue_style('mailjet_css', plugins_url('/assets/mailjet.css', __FILE__));
        wp_register_script('mailjet_js', plugins_url('/assets/js/mailjet.js', __FILE__), array('jquery'));
        wp_enqueue_script('mailjet_js');
        $this->addMjJsGlobalVar();
    }

    public function addMjJsGlobalVar()
    {
        $mjGlobalVars = array();
        $mjGlobalVarsProps = array();
        $mjWidgetDb = get_option('widget_wp_mailjet_subscribe_widget');
        if ($mjWidgetDb === false) {
            return;
        }
        foreach ($this->langs as $lang => $langProps) {
            foreach (array('metaProperty1' . $lang, 'metaPropertyName1' . $lang, 'metaProperty2' . $lang, 'metaPropertyName2' . $lang,
                         'metaProperty3' . $lang, 'metaPropertyName3' . $lang) as $prop) {
                foreach ($mjWidgetDb as $widgetId => $instance) {
                    if (!empty($instance[$prop])) {
                        $mjGlobalVars[$widgetId] = $instance[$prop];
                    }
                }
            }
        }

        foreach($mjWidgetDb as $widgetId => $instance){
            if (!is_array($instance)) {
                continue;
            }
            foreach ($instance as $instanceKey => $prop){
                if (stristr($instanceKey, 'metaPropertyName')) {
                    $iLang = explode('metaPropertyName', $instanceKey);
                    if (!empty($instance['metaProperty' . $iLang[1]])) {
                        $mjGlobalVarsProps[$widgetId][$prop . substr($iLang[1], 1)] = $instance['metaProperty' . $iLang[1]];
                    }
                }
            }
        }

        ?>
        <script type="text/javascript">
            var mjGlobalVars = <?php echo json_encode($mjGlobalVars); ?>;
            var mjGlobalVarsProps = <?php echo json_encode($mjGlobalVarsProps); ?>;
        </script>
        <?php
    }

    public function display_menu()
    {
        if (function_exists('add_submenu_page')) {
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

    function phpmailer_init_smtp(PHPMailer $phpmailer)
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
            'APIKey' => get_option('mailjet_username'), // Use any API Key from your Sub-accounts
            'SecretKey' => get_option('mailjet_password'),
            'MailjetToken' => get_option('mailjet_token' . $_SERVER['REMOTE_ADDR'])
        ));

        // Return FALSE if there is token
        if (isset($token->Status) && $token->Status == 'ERROR')
            return FALSE;

        return $token;
    }

    /**
     * This method returns the current locale of the wordpress' user
     */
    private function _get_locale()
    {
        $locale = get_locale();
        if (!in_array($locale, array('fr_FR', 'en_US', 'en_GB', 'en_EU', 'de_DE', 'es_ES'))) {
            $locale = 'en_US';
        }
        return $locale;
    }

    public function show_campaigns_menu()
    {
        echo '<div class="wrap"><div class="icon32"><img src="' . plugin_dir_url(__FILE__) . '/assets/images/mj_logo_med.png' . '" /></div><h2>';
        echo __('Campaigns', 'wp-mailjet');
        echo '</h2></div>';
        echo '<iframe width="980px" height="1200" src="https://' . (($this->api->version == '0.1') ? 'www' : (($this->api->version == 'REST') ? 'app' : 'www')) . '.mailjet.com/campaigns?t=' . $this->_get_auth_token() . '&show_menu=none&u=WordPress-3.1&f=amc&locale=' . $this->_get_locale() . '"></iframe>';
    }

    public function show_stats_menu()
    {
        echo '<div class="wrap"><div class="icon32"><img src="' . plugin_dir_url(__FILE__) . '/assets/images/mj_logo_med.png' . '" /></div><h2>';
        echo __('Statistics', 'wp-mailjet');
        echo '</h2></div>';
        echo '<iframe width="980px" height="1200" src="https://' . (($this->api->version == '0.1') ? 'www' : (($this->api->version == 'REST') ? 'app' : 'www')) . '.mailjet.com/stats?t=' . $this->_get_auth_token() . '&show_menu=none&u=WordPress-3.1&f=amc&locale=' . $this->_get_locale() . '"></iframe>';
    }

    public function show_contacts_menu()
    {
        echo '<div class="wrap"><div class="icon32"><img src="' . plugin_dir_url(__FILE__) . '/assets/images/mj_logo_med.png' . '" /></div><h2>';
        echo __('Contacts', 'wp-mailjet');
        echo '</h2></div>';
        echo '<iframe width="980px" height="1200" src="https://' . (($this->api->version == '0.1') ? 'www' : (($this->api->version == 'REST') ? 'app' : 'www')) . '.mailjet.com/contacts/lists?t=' . $this->_get_auth_token() . '&show_menu=none&u=WordPress-3.1&f=amc&locale=' . $this->_get_locale() . '"></iframe>';
    }
}