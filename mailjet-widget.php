<?php
/**
 * Mailjet Wordpress Plugin
 *
 * @author        Mailjet
 * @link        http://www.mailjet.com/
 *
 */
class WP_Mailjet_Subscribe_Widget extends WP_Widget
{
    protected $api;
    private $lists = FALSE;
    private $_userVersion = FALSE;
    public $entriesen;
    public $poParseren;
    public $entriesfr;
    public $poParserfr;
    public $entriesde;
    public $poParserde;
    public $entrieses;
    public $poParseres;
    public $locale;
    public $langs = array(
        'en' => array('locale' => 'en_US', 'label' => 'English'),
        'fr' => array('locale' => 'fr_FR', 'label' => 'French'),
        'de' => array('locale' => 'de_DE', 'label' => 'German'),
        'es' => array('locale' => 'es_ES', 'label' => 'Spanish'),
    );

    const MAX_META_PROPERTIES = 3;
    const WIDGET_HASH = '[\^=l|>5i!? {xI';

    public function __construct()
    {
        // Set Plugin URL
        $chunks = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
        $this->pluginUrl = WP_PLUGIN_URL . '/' . end($chunks);

        $this->locale = get_locale() === 'en_US' ? 'en_EN' : get_locale();

        //No dependency injection possible, so we have to use this:
        $this->api = new WP_Mailjet_Api(get_option('mailjet_username'), get_option('mailjet_password'));

        $widget_ops = array(
            'classname' => 'WP_Mailjet_Subscribe_Widget',
            'description' => 'Allows your visitors to subscribe to one of your lists'
        );

        parent::__construct(FALSE, __('Mailjet Subscription widget'), $widget_ops);
        add_action('wp_ajax_mailjet_subscribe_ajax_hook', array($this, 'mailjet_subscribe_from_widget'));
        add_action('wp_ajax_nopriv_mailjet_subscribe_ajax_hook', array($this, 'mailjet_subscribe_from_widget'));
        add_action('wp_ajax_mailjet_subscribe_ajax_add_meta_property', array($this, 'wp_ajax_mailjet_subscribe_ajax_add_meta_property'));

        require_once dirname(__FILE__) . '/libs/PHP-po-parser-master/src/Sepia/InterfaceHandler.php';
        require_once dirname(__FILE__) . '/libs/PHP-po-parser-master/src/Sepia/FileHandler.php';
        require_once dirname(__FILE__) . '/libs/PHP-po-parser-master/src/Sepia/PoParser.php';

        foreach ($this->langs as $lang => $langProps) {
            $fileHandler = new FileHandler(dirname(__FILE__) . '/i18n/wp-mailjet-subscription-widget-' . $langProps['locale'] . '.po');
            $this->{'poParser' . $lang} = new PoParser($fileHandler);
            $this->{'entries' . $lang}  = $this->{'poParser' . $lang}->parse();
        }

        // if user clicks on the email confirm subscription link, verify the token and subscribe them
        if (!empty($_GET['mj_sub_token'])) {
            $this->subscribeUser();
        }
    }

    function wp_ajax_mailjet_subscribe_ajax_add_meta_property()
    {
        if (!empty($_REQUEST['name']) && !empty($_REQUEST['type'])) {
            echo json_encode($this->api->createMetaContactProperty(array(
                'name' => $_REQUEST['name'],
                'dataType' => $_REQUEST['type']
            )));
        }
        die;
    }

    /**
     * Get list of contact lists
     *
     * @param void
     * @return (array) $this->lists
     */
    function getLists()
    {
        if ($this->lists === FALSE) {
            $this->lists = $this->api->getContactLists(array('limit' => 0));
            if (isset($this->lists->Status) && $this->lists->Status == 'ERROR')
                $this->lists = array();
        }
        return $this->lists;
    }

    function getContactMetaProperties($showMsg = true)
    {
        $response = $this->api->getContactMetaProperties(array(
            'method' => 'GET',
            'limit' => 0
        ));
        $this->_userVersion = 3;
        if (empty($response)) {
            if ($showMsg === true) {
                echo '<p class="error">';
                echo __('You are either v1 user or we could not fetch user contact properties. Please contact our <a target="_blank" href="https://www.mailjet.com/support">support</a> team to discuss migrating to v3 user where you will have contact properties available. You can still configure a Mailjet subscription widget by clicking on "Next" button and complete step2 and step3.', 'wp-mailjet-subscription-widget');
                echo '</p>';
            }
            $this->_userVersion = 1;
        }
        return $response;
    }

    function preg_array_key_exists($pattern, $array)
    {
        return (int)preg_grep($pattern, array_keys($array));
    }

    function form($instance)
    {
        global $WPMailjet;

        // if there are translation entries in the POST, update them
        if($this->preg_array_key_exists('/msgstr/', $_POST)){
            require_once(dirname(__FILE__) . '/libs/php.mo-master/php-mo.php');
            foreach ($this->langs as $lang => $locale) {
                foreach($_POST as $key => $value) {
                    if(substr($key, 0, 7) === 'msgstr-'){
                        foreach($this->{'entries' . $lang} as $entryKey => $entryValue){
                            if($this->entryStrToId($entryKey) === str_replace('msgstr-' . $lang . '-', '', $key)){
                                $this->{'entries' . $lang}[$entryKey]['msgstr'][0] = stripcslashes($_POST[$key]);
                                $this->{'poParser' . $lang}->setEntry($entryKey, $this->{'entries' . $lang}[$entryKey]);
                            }
                        }
                    }
                }
            }
            foreach ($this->langs as $lang => $locale) {
                $this->{'poParser' . $lang}->writeFile(dirname(__FILE__) . '/i18n/wp-mailjet-subscription-widget-' . $locale['locale'] . '.po');
                phpmo_convert(dirname(__FILE__) . '/i18n/wp-mailjet-subscription-widget-' . $locale['locale'] . '.po');
            }
            if (in_array(get_locale(), array('en_US', 'en_EN', ''))) {
                foreach(array('-en_US', '-en_EN', '') as $lang) {
                    $this->poParseren->writeFile(dirname(__FILE__) . '/i18n/wp-mailjet-subscription-widget' . $lang . '.po');
                    phpmo_convert(dirname(__FILE__) . '/i18n/wp-mailjet-subscription-widget' . $lang . '.po');
                }
            }
        }

        $fields = array('new_meta_name', 'new_meta_data_type');
        $langFields = array('enableTab', 'title', 'list_id', 'button_text', 'metaPropertyName1', 'metaPropertyName2',
            'metaPropertyName3', 'metaProperty1', 'metaProperty2', 'metaProperty3');
        foreach ($fields as $prop) {
            ${$prop} = empty($instance[$prop]) ? '' : $instance[$prop];
        }
        foreach($langFields as $prop){
            foreach($this->langs as $lang => $langProps){
                ${$prop . $lang} = empty($instance[$prop . $lang]) ? '' : $instance[$prop . $lang];
            }
        }

        ?>
        <div class="mailjet-widget-container">
        <div class="mj-accordion">
            <h3>Step 1 - Choose up to 3 contact properties</h3>
            <div>
                <?php
                $contactMetaProperties = $this->getContactMetaProperties();
                if ($this->_userVersion === 3): ?>
                <?php
                if (empty($contactMetaProperties->Data)): ?>
                    <div class="fontSizeSmall noProperties"><?php echo sprintf('No contact properties have been defined yet. Please create one by clicking on "Add New Property" below or by logging into your Mailjet account.'); ?></div>
                    <div class="fontSizeSmall yesProperties" style="display:none;"><?php echo sprintf('Drag and drop up to %d contact properties from "Available contact properties" to "Selected contact properties" (besides email which is mandatory).', self::MAX_META_PROPERTIES); ?></div>
                    <div class="label yesProperties" style="display:none;">Available contact properties</div>
                <?php else: ?>
                    <div class="fontSizeSmall yesProperties"><?php echo sprintf('Drag and drop up to %d contact properties from "Available contact properties" to "Selected contact properties" (besides email which is mandatory).', self::MAX_META_PROPERTIES); ?></div>
                    <div class="label">Available contact properties</div>
                <?php endif; ?>
                <div class="sortable" <?php  if (empty($contactMetaProperties->Data)): ?> style="display:none;"  <?php endif; ?> >
                    <div>
                        <ul class="connectedSortable sortable1">
                            <?php foreach ($contactMetaProperties->Data as $prop):
                                $lang = 'en';
                                    //foreach($this->langs as $lang => $langProps): ?>
                                        <?php if (!in_array($prop->Name, array(${'metaPropertyName1' . $lang}, ${'metaPropertyName2' . $lang}, ${'metaPropertyName3' . $lang}))): ?>
                                            <li class="ui-state-default"><div class="cursorMoveImg"></div>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $prop->Name; ?></li>
                                        <?php endif; ?>
                                    <?php //endforeach; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div>
                        <div class="label">Selected properties</div>
                        <div class="fontSizeSmall">Arrange and sort the properties you selected to determine the way they will be shown in the widget.</div>
                        <ul class="connectedSortable sortable2">
                            <li class="ui-state-disabled">Email address (mandatory)</li>
                            <?php $selectedProps = array();
                                foreach ($instance as $prop):
                                    if (empty($prop) || in_array($prop, $selectedProps)) {
                                        continue;
                                    }
                                    $lang = 'en'; ?>
                                    <?php if (in_array($prop, array(${'metaPropertyName1' . $lang}, ${'metaPropertyName2' . $lang}, ${'metaPropertyName3' . $lang}))): ?>
                                        <li class="ui-state-default"><div class="cursorMoveImg"></div>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $prop; ?></li>
                                        <?php $selectedProps[] = $prop; ?>
                                    <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <div class="new-meta-contact-form">
                    <div class="label">Add New Property</div>
                    <div>Click on the button below to dynamically add a new contact property to your contact list and widget.</div>
                    <ul class="newPropertyBtn">
                        <li>Add a new property</li>
                    </ul>
                    <?php if (isset($metaAdded)): ?>
                        <div class="success">Property created. Please drag your new contact property to the Selected Properties section above.</div>
                    <?php endif; ?>
                    <div class="newPropertyForm ninja">
                        <div class="new-meta-property-response"></div>
                        <div>
                            <label>Name</label><br/>
                            <input type="text" name="new_meta_name" class="new_meta_name"
                                title="Please make sure you are not using forbidden characters: , * + - / &quot; ' : [ ] ( ) > < = ; $"/>
                        </div>
                        <div>
                            <label>Contact property type</label><br/>
                            <select name="new_meta_data_type" class="new_meta_data_type">
                                <option value="str">String (ex. FirstName)</option>
                                <option value="int">Integer (ex. 90210)</option>
                                <option value="float">Decimal (ex. 6245.538)</option>
                                <option value="bool">Bool</option>
                            </select>
                        </div>
                        <p>
                            <input type="submit" name="submit" class="button new-meta-submit" value="Add"/>
                            <input name="action" type="hidden" value="mailjet_subscribe_ajax_add_meta_property"/>
                        </p>
                    </div>
                </div>
                    <?php endif; ?>
                <p>
                    <input type="submit" class="next floatRight button" value="Next"/>
                </p>
            </div>

            <h3>Step 2 - Define your widget labels</h3>
            <div>
                <div class="mj-tabs-container">
                    <ul class="mj-tabs-menu">
                        <?php foreach ($this->langs as $lang => $langProps): ?>
                            <li class="<?php echo $lang === 'en' ? 'current' : ''; ?>">
                                <a href=".tab-<?php echo $lang; ?>"><?php echo $langProps['label']; ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="mj-tab">
                        <?php foreach ($this->langs as $lang => $langProps): ?>
                            <div class="mj-tab-content tab-<?php echo $lang; ?>">
                                <div class="tabActivator<?php echo $lang === 'en' ? ' ninja' : ''; ?>" id="<?php echo $this->get_field_id('tabActivator-' . $lang); ?>">
                                    <input type="checkbox" name="<?php echo $this->get_field_name('enableTab' . $lang); ?>"
                                           class="enable-tab enable-tab-<?php echo $lang; ?>" id="<?php echo $this->get_field_id('enableTab' . $lang); ?>"
                                           <?php echo $lang === 'en' ? 'checked="checked"' :
                                               (empty(${'enableTab'.$lang}) ? '' : 'checked="checked"'); ?> />
                                    <label for="<?php echo $this->get_field_id('enableTab' . $lang); ?>">Activate tab</label>
                                </div>
                                <!-- This element will be populated with input text fields for each selected meta property -->
                                <div class="fontSizeSmall">Please enter specific labels for your subscription widget and they
                                    will be displayed on the front end of your website.</div>
                                <?php if ($this->_userVersion === 3): ?>
                                    <div class="clear map-meta-properties">
                                    <?php for($i = 1; $i <= 3; $i++): ?>
                                        <div class="<?php echo $this->get_field_id('metaProperty' . $i . $lang); ?>">
                                            <label for="<?php echo $this->get_field_id('metaProperty' . $i . $lang); ?>"><?php
                                                echo esc_attr(${'metaPropertyName' . $i . $lang}); ?></label>
                                            <input type="hidden" id="<?php echo $this->get_field_id('metaPropertyName' . $i . $lang); ?>"
                                                name="<?php echo $this->get_field_name('metaPropertyName' . $i . $lang); ?>"
                                                value="<?php echo esc_attr(${'metaPropertyName' . $i . $lang}); ?>" />
                                            <input class="widefat"
                                                id="<?php echo $this->get_field_id('metaProperty' . $i . $lang); ?>"
                                                name="<?php echo $this->get_field_name('metaProperty' . $i . $lang); ?>"
                                                type="text"
                                                value="<?php echo empty(${'metaProperty' . $i . $lang}) ? '' : esc_attr(${'metaProperty' . $i . $lang}); ?>"
                                                size="28" />
                                        </div>
                                    <?php endfor; ?>
                                    </div>
                                <?php endif; ?>
                                <p>
                                    <label for="<?php echo $this->get_field_id('title' . $lang); ?>"
                                        title="This is the title of your subscription widget which will be displayed on your website">Title:
                                        <input class="widefat" id="<?php echo $this->get_field_id('title' . $lang); ?>"
                                            name="<?php echo $this->get_field_name('title' . $lang); ?>" type="text"
                                            value="<?php echo esc_attr(${'title' . $lang}); ?>"/>
                                    </label>
                                </p>
                                <p>
                                    <label for="<?php echo $this->get_field_id('button_text' . $lang); ?>"
                                        title="This will be the label of your subscription button which will be displayed on your website">Button text
                                        <input class="widefat" id="<?php echo $this->get_field_id('button_text' . $lang); ?>"
                                            name="<?php echo $this->get_field_name('button_text' . $lang); ?>" type="text"
                                            value="<?php echo esc_attr(${'button_text' . $lang}); ?>"/>
                                    </label>
                                </p>
                                <p>
                                    <label for="<?php echo $this->get_field_id('list_id' . $lang); ?>"
                                        title="Please chose a contact list where all new subscribers will be added">List:
                                        <select class="widefat" <?php echo (isset($_POST) && count($_POST) > 0 && !is_numeric(${'list_id' . $lang})) ?
                                            'style="border:1px solid red;' : '' ?> id="<?php echo $this->get_field_id('list_id' . $lang); ?>"
                                            name="<?php echo $this->get_field_name('list_id' . $lang); ?>">
                                            <?php foreach ($this->getLists() as $list) { ?>
                                                <option value="<?php echo $list['value'] ?>"<?php echo($list['value'] == esc_attr(${'list_id' . $lang}) ?
                                                    ' selected="selected"' : '') ?>><?php echo $list['label'] ?></option>
                                            <?php } ?>
                                        </select>
                                    </label>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p>
                        <input type="submit" class="previous button" value="Back"/>
                        <input type="submit" class="next floatRight button" value="Next"/>
                    </p>
                </div>
            </div>

            <h3>Step 3 - Customize your widget notifications</h3>
            <div>
                <div class="mj-tabs-container">
                    <ul class="mj-tabs-menu">
                        <?php foreach ($this->langs as $lang => $langProps): ?>
                            <li class="<?php echo $lang === 'en' ? 'current' : ''; ?>">
                                <a href=".tab-<?php echo $lang; ?>"><?php echo $langProps['label']; ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="mj-tab">
                        <?php foreach ($this->langs as $lang => $langProps): ?>
                        <div class="mj-tab-content tab-<?=$lang?>">
                            <div class="mj-translations-title margin-top-bottom-10 arrow_box" id="mj-translations-title-<?=$lang?>">
                                <div style="display:none;">Widget notifications for this language are deactivated in previous step</div>
                                <a id="<?php echo $this->get_field_id('tabLinkStep3-' . $lang); ?>" href="javascript:">Customize your widget notifications</a></div>
                            <div class="mj-string-translations" id="<?php echo $this->get_field_id('mj-string-translations-' . $lang); ?>">
                                <?php
                                $i = 0;
                                foreach ($this->{'entries' . $lang} as $msgid => $msg):
                                    $id = $this->entryStrToId($msgid);
                                    $i++;
                                    if($i === 1):
                                ?>
                                    <h4>Errors/Website notifications</h4>
                                    <!--The 1st 10 messages are for website notifications, the rest are for the email-->
                                    <?php elseif($i === 10): ?>
                                        <h4>Subscription confirmation mail</h4>
                                    <?php endif; ?>
                                    <div class="mj-translation-entry">
                                        <h6><?php echo $msgid; ?></h6>
                                            <textarea name="msgstr-<?php echo $lang; ?>-<?php echo $id; ?>" id="msgstr-<?php echo $lang; ?>-<?php echo $id; ?>"
                                                rows="5" cols="30"><?php echo $msg['msgstr'][0]; ?></textarea>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                         <?php endforeach; ?>
                    </div>
                    <p><input type="submit" class="previous button" value="Back"/></p>
                </div>
            </div>
        </div>
        </div>
        <?php if (isset($_POST) && count($_POST) > 0): ?>
        <div class="mailjet_subscribe_response"><?php
            echo 'Success! Please go to your wordpress site to see your widget in action.' ?></div>
    <?php endif;
        $WPMailjet->addMjJsGlobalVar();
    }

    function entryStrToId($str)
    {
        return substr(str_replace(array(' ', '%', ':', '/', '<', '>', '"', '\\', '@', '.', '!', '?'), array('-'), $str), 0, 20);
    }

    function update($new_instance, $old_instance)
    {
        return $new_instance;
    }

    /**
     * Checks if the string argument is integer.
     * @param string $input
     * @return bool
     */
    function mj_is_int($input) {
        return ctype_digit(strval($input));
    }

    /**
     * Checks if the string argument is float.
     * @param string $input
     * @return bool
     */
    function mj_is_float($input) {
        return $input === (string)(float)$input;
    }

    /**
     * Checks if the string argument is boolean.
     * @param string $input
     * @return bool
     */
    function mj_is_bool($input) {
        return in_array(strtolower($input), array('1', 't', 'true', 'y', '0', 'f', 'false', 'n'));
    }

    /**
     * Email the collected widget data to the customer with a verification token
     * @param void
     * @return void
     */
    public function mailjet_subscribe_from_widget()
    {
        $error = empty($_POST['email']) ? 'Email field is empty' : false;
        $error = empty($_POST['list_id']) ? 'Missing list id' : $error;
        if (false !== $error) {
            _e($error, 'wp-mailjet-subscription-widget');
            die;
        }

        if (!$this->validate_email($_POST['email'])) {
            _e('Invalid email', 'wp-mailjet-subscription-widget');
            die;
        }

        $recipient = $this->api->findRecipient(array(
            'ContactsList' => $_POST['list_id'],
            'ContactEmail' => $_POST['email'],
        ));

        if (!empty($recipient->Count) || $recipient === true) {
            echo '<p class="error" listId="' . $_POST['list_id'] . '">';
            echo sprintf(__("The contact %s is already subscribed", 'wp-mailjet-subscription-widget'), $_POST['email']);
            echo '</p>';
            die;
        }

        $metaProperties = $this->getContactMetaProperties(false);

        if (!empty($metaProperties->Count) && !empty($metaProperties->Data)) {
            // check if the input from contact meta properties matches the data type of each property
            // apiDataType => phpCheckFunction
            $dataTypes = array(
                'int' => 'mj_is_int',
                'float' => 'mj_is_float',
                'bool' => 'mj_is_bool'
            );
            $error = false;
            $submittedProperties = array_diff(array_keys($_POST), array('email', 'list_id', 'action'));
            foreach ($metaProperties->Data as $accountProperty) {
                foreach ($submittedProperties as $submittedProperty) {
                    if ($accountProperty->Datatype === 'str') {
                        continue;
                    }
                    if ($accountProperty->Name === $submittedProperty &&
                        $this->$dataTypes[$accountProperty->Datatype]($_POST[$submittedProperty]) !== true) {
                        $error = 'You have entered a contact property with wrong data type, for example a string instead of a number.';
                    }
                }
            }
            if (false !== $error) {
                _e($error, 'wp-mailjet-subscription-widget');
                die;
            }
        }

        $params = http_build_query($_POST);
        $message = file_get_contents(dirname(__FILE__) . '/templates/confirm-subscription-email.php');
        $emailParams = array(
            '__EMAIL_TITLE__' => __('Confirm your mailing list subscription', 'wp-mailjet-subscription-widget'),
            '__EMAIL_HEADER__' => __('Please Confirm Your Subscription To', 'wp-mailjet-subscription-widget'),
            '__WP_URL__' => sprintf('<a href="%s" target="_blank">%s</a>', get_site_url(), get_site_url()),
            '__CONFIRM_URL__' => get_site_url() . '?' . $params . '&mj_sub_token=' . sha1($params . self::WIDGET_HASH),
            '__CLICK_HERE__' => __('Click here to confirm', 'wp-mailjet-subscription-widget'),
            '__COPY_PASTE_LINK__' => __('You may copy/paste this link into your browser:', 'wp-mailjet-subscription-widget'),
            '__FROM_NAME__' => get_option('blogname'),
            '__IGNORE__' => __('Did not ask to subscribe to this list? Or maybe you have changed your mind? Then simply ignore this email and you will not be subscribed', 'wp-mailjet-subscription-widget'),
            '__THANKS__' => __('Thanks,', 'wp-mailjet-subscription-widget')
        );
        foreach ($emailParams as $key => $value) {
            $message = str_replace($key, $value, $message);
        }
        add_filter('wp_mail_content_type', create_function('', 'return "text/html"; '));
        wp_mail($_POST['email'], __('Subscription Confirmation', 'wp-mailjet-subscription-widget'), $message,
            array('From: ' . get_option('blogname') . ' <' . get_option('admin_email') . '>'));
        echo '<p class="success">' . __('Subscription confirmation email sent. Please check your inbox and confirm the subscription.',
                'wp-mailjet-subscription-widget') . '</p>';
        die;
    }

    /**
     * Subscribe the user from the widget
     */
    function subscribeUser()
    {
        // validate token
        $mj_sub_token = $_GET['mj_sub_token'];
        unset($_GET['mj_sub_token']);
        if (sha1(http_build_query($_GET) . self::WIDGET_HASH) !== $mj_sub_token) {
            echo '<p class="error" listId="' . $_GET['list_id'] . '">';
            echo __('Error. Token verification failed.', 'wp-mailjet-subscription-widget');
            echo '</p>';
            die;
        }

        $email = $_GET['email'];

        // Add the contact to the contact list
        $result = $this->api->addContact(array(
            'Email' => $email,
            'ListID' => $_GET['list_id']
        ));

        $metaProperties = $this->getContactMetaProperties(false);

        $properties = array();
        if (is_object($metaProperties)) {
            foreach ($metaProperties->Data as $i => $prop) {
                if (!array_key_exists($prop->Name, $_GET)) {
                    continue;
                }
                $properties[] = array(
                    'Name' => $prop->Name,
                    'Value' => $_GET[$prop->Name]
                );
            }
        }

        if (!empty($result->Response->Data)) {
            $result2 = $this->api->updateContactData(array(
                'method' => 'JSON',
                'ID' => $email,
                'Data' => $properties
            ));
        }

        // Check what is the response and display proper message
        if (isset($result->Status)) {
            if ($result->Status == 'DUPLICATE') {
                echo '<p class="error" listId="' . $_GET['list_id'] . '">';
                echo sprintf(__("The contact %s is already subscribed", 'wp-mailjet-subscription-widget'), $email);
                echo '</p>';
            } else if ($result->Status == 'OK') {
                echo '<p class="success" listId="' . $_GET['list_id'] . '">';
                echo sprintf(__("Thanks for subscribing with %s", 'wp-mailjet-subscription-widget'), $email);
                echo '</p>';
            } else {
                echo '<p class="error" listId="' . $_GET['list_id'] . '">';
                echo sprintf(__("The contact %s is already subscribed", 'wp-mailjet-subscription-widget'), $email);
                echo '</p>';
            }
            die();
        }
    }

    function widget($args, $instance)
    {
        $this->getContactMetaProperties(false);
        // enqueue the scripts required for the widget (only if the widget is active)
        // scripts will appear in the footer which is good for speed
        wp_enqueue_script('ajax-example', $this->pluginUrl . '/assets/js/ajax.js', array('jquery'));
        wp_localize_script('ajax-example', 'WPMailjet', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ajax-example-nonce'),
            'loadingImg' => plugin_dir_url(__FILE__) . 'assets/images/loading.gif'
        ));

        // output the widget itself
        extract($args, EXTR_SKIP);

        echo $before_widget;

        $langFields = array('enableTab', 'title', 'list_id', 'button_text', 'metaPropertyName1', 'metaPropertyName2',
            'metaPropertyName3', 'metaProperty1', 'metaProperty2', 'metaProperty3');
        if(isset($fields) && is_array($fields))
        foreach($fields as $prop){
            ${$prop} = empty($instance[$prop]) ? '' : $instance[$prop];
        }

        foreach($this->langs as $lang => $langProps) {
            if (get_locale() !== $langProps['locale']) {
                continue;
            }
            $currentLang = $lang;
        }

        // if the widget is not configured for the current WP language, the English widget configuration is taken
        $currentLang = empty($currentLang) ? 'en' : $currentLang;

        foreach($langFields as $prop){
            if (!empty($instance[$prop . $currentLang])) {
                ${$prop . $currentLang} = $instance[$prop . $currentLang];
            }
        }

        $title = apply_filters('widget_title', $instance['title' . $currentLang]);
        $list_id = $instance['list_id' . $currentLang];
        $button_text = trim($instance['button_text' . $currentLang]);

        // If contact list is not selected then we just don't display the widget!
        if (!is_numeric($list_id)) {
            return FALSE;
        }

        if (!empty($title))
            echo $before_title . $title . $after_title;;
        ?>

        <!--WIDGET CODE GOES HERE-->
        <form class="subscribe-form">
            <?php if ($this->_userVersion === 3): ?>
                <?php if (!empty(${'metaProperty1' . $currentLang})): ?>
                    <input name="<?php echo ${'metaPropertyName1' . $currentLang}; ?>" type="text" placeholder="<?php echo ${'metaProperty1' . $currentLang}; ?>"/>
                <?php endif; ?>
                <?php if (!empty(${'metaProperty2' . $currentLang})): ?>
                    <input name="<?php echo ${'metaPropertyName2' . $currentLang}; ?>" type="text" placeholder="<?php echo ${'metaProperty2' . $currentLang}; ?>"/>
                <?php endif; ?>
                <?php if (!empty(${'metaProperty3' . $currentLang})): ?>
                    <input name="<?php echo ${'metaPropertyName3' . $currentLang}; ?>" type="text" placeholder="<?php echo ${'metaProperty3' . $currentLang}; ?>"/>
                <?php endif; ?>
            <?php endif; ?>

            <input id="email" name="email" type="text"
                   placeholder="<?php _e('your@email.com', 'wp-mailjet-subscription-widget'); ?>"/>
            <input name="list_id" type="hidden" value="<?php echo $list_id; ?>"/>
            <input name="action" type="hidden" value="mailjet_subscribe_ajax_hook"/>
            <input name="submit" type="submit" class="mailjet-subscribe" value="<?php echo __($button_text); ?>">
        </form>
        <div class="response"></div>
        <?php
        wp_register_script('mailjet_js', plugins_url('/assets/js/mailjet.js', __FILE__), array('jquery'));
        wp_enqueue_script('mailjet_js');
        echo $after_widget;
    }

    function validate_email($email)
    {
        return (preg_match("/(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)/", $email) ||
            !preg_match("/^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/", $email)) ? FALSE : TRUE;
    }
}
