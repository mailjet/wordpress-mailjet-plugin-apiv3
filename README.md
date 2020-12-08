# Mailjet Email Newsletter Marketing

- Contributors: Mailjet
- Tags: email, marketing, signup, newsletter, widget, smtp, woocommerce, contact form 7
- Requires at least: 4.4
- Tested up to: 5.6
- Stable tag: 5.2.5
- Requires PHP: 5.6
- License: GPLv2 or later
- License URI: http://www.gnu.org/licenses/gpl-2.0.html

Includes WooCommerce automated and order emails. Design, send and track engaging marketing and transactional emails from your WordPress admin.

## What's new
* Full **WooCommerce support** - use Mailjet's drag and drop email builder to create responsive *Order Notification* and *Abandoned cart reminder* emails and engage with your customers at the right moment. Add newsletter subscribers from the "Check out" and the "Thank you" pages. Sync order data to Mailjet and use our powerful *Segmentation* and *Automation* features to target specific customer groups.
* **Shortcode support** - add the subscription widget in any page or post using shortcode.
* Integration with **Contact Form 7** - add a "Subscribe to our newsletter" checkbox to any Contact Form 7 form and easily add subscribers to your contact lists

## Description

Mailjet's official plugin on WordPress gives you access to:

* **Easy Email Management:** Create and manage all of your marketing and transactional email campaigns directly from your WordPress admin.

* **Sign Up Form & Contact Lists Management:** Create and customize contact form widgets to allow your website visitors to subscribe to your newsletters. Add the subscription widget in any page or post using shortcode.

* **Automated Contact Synchronization** - 4 ways to build your contact lists:

   * Synchronize your *WordPress users*. The user role is added to Mailjet as a contact property, so you can filter and target marketing emails to the proper group. With ongoing synchronization, you never have to think about contact management again.
   
   * *Comment authors* can be added to a separate Mailjet contact list as they choose to subscribe while posting a comment on the blog.
   
   * Subscribe *WooCommerce customers* to your newsletter during checkout. Just enable the WooCommerce integration inside the plugin and you are ready to go.
   
   * Use the built in *Contact Form 7* integration and allow form submitters to subscribe to your newsletter.

* **Campaign Builder Tool:** Use our drag and drop email editor or HTML builder to create beautiful and engaging emails - directly from your WordPress admin.

* **Flexibility** - use filters to set your own subscription confirmation email template or texts inside the email, set your own thank you page, or widget form. See the FAQ for more details.

* **World Class Deliverability:** Hit your subscribers' inboxes every time with our global deliverability and routing infrastructure

* **Insight and analytics:** Access real-time statistics on your campaigns showing opens, clicks, geographies, average time to click and more to optimize your email performance.

* **Data Compliance:** Mailjet is GDPR compliant and ISO 27001 certified, meaning that it guarantees an optimal level of email data privacy and security.

* **International UI and Support:** Mailjet offers user interfaces, documentation and 24/7 customer support in 5 languages (English, French, German, Spanish and Italian).

## Installation

1. Log in as administrator in Wordpress.
2. Go to Plugins > Add New
3. Find "Mailjet Email Marketing" in the plugins directory and install it **or** Click "Upload plugin" and upload the `mailjet-for-wordpress.zip` file from GitHub
4. Activate the Mailjet extension through the "Plugins" menu in WordPress.

**Note:** You must have cURL extension enabled. PHP 5.6 or later version is required.

## Frequently Asked Questions

##### What is Mailjet?
[Mailjet](http://www.mailjet.com?aff=wordpressmj) is an all-in-one solution to send, track and deliver both marketing and transactional emails. Its cloud-based infrastructure is unique and highly scalable with a proprietary technology that optimizes email deliverability. Mailjet can be accessed either via an easy-to-use online drag-and-drop interface or via APIs that allow developers to integrate its features within their online app or service, or its sophisticated SMTP relay.

##### Why use Mailjet on WordPress?
- Because you don't have time to build a global leading email infrastructure and have a product or service to build and grow.
- Because you want to compose responsive Newsletters that engage your users and boost your traffic or revenue without having to code.
- Because you want the latest in email tracking technology that shows you which users open and click on your newsletters, when, on what device, on which links, etc.
- Because you want to contact a Support Helpdesk that works when you do whether you're a morning person or a night owl.
- Because you want to easily subscribe new users to your contact lists and not have to worry about keeping them in sync.
- Because you want to make sure your emails get delivered to the inbox!

##### Do I need a Mailjet Account?
Yes. You can [create one for free](https://app.mailjet.com/signup?aff=wordpressmj): it's easy and it only takes a few minutes.

##### How to get started with this plugin?
Once you have a Mailjet account, grab your [Mailjet API credentials](https://app.mailjet.com/account/api_keys) and activate the plugin. An installation wizard will guide you through. 
For more help on setting up the Mailjet Plugin for WordPress, feel free to check out our [dedicated WordPress User Guide](https://www.mailjet.com/guides/wordpress-user-guide?aff=wordpressmj).

##### How do I create a signup form or use the contact widget?
Once your Mailjet plugin is installed and configured, click on "Appearance" in the left-side WordPress admin menu and then choose the "Widgets" section. Drag the "Mailjet Subscription Widget" widget and drop it where you want it to appear (i.e. the sidebar). For more details, please visit the official help page [Adding Widgets](http://en.support.wordpress.com/widgets/#adding-widgets).

##### In which languages is this plugin available?
The Mailjet Plugin is available in English, Spanish, French, German and Italian.
Need help? Our multilingual support team is here to answer your questions in any of these languages, any day of the week, at any time via our [online helpdesk](https://app.mailjet.com/support).

#### Using filters ####
##### Customize the subscription confirmation email template
Add the following code to your template functions.php file. Uncomment the messages that you would like to replace.
<pre><code>
/**
 * Override subscription confirmation email texts
 * @param array $emailData -default Mailjet email template parameters
 * @return string
 */

function updateMailjetSubscriptionEmailParameters($emailData) {
add_filter( 'mailjet_subscription_widget_email_params', 'updateMailjetSubscriptionEmailParameters' );

    // Some custom parameters used from custom template added via `mailjet_confirmation_email_filename`
    // $emailData['TITLE'] = 'Custom title';
    // $emailData['SOME_HEADER'] = 'Custom header';
    // $emailData['CONFIRM'] = 'Custom confirm';
    // $emailData['SOME_FOOTER'] = 'Custom footer';

    // Override default mailjet parameters
    // $emailData['__EMAIL_TITLE__'] = 'Please confirm your subscription';
    // $emailData['__EMAIL_HEADER__'] = 'To receive newsletters from __WP_URL__ please confirm your subscription by clicking the following button:';
    // $emailData['__CLICK_HERE__'] = 'Yes, subscribe me to this list';
    // $emailData['__COPY_PASTE_LINK__'] = 'You may copy/paste this link into your browser:';
    // $emailData['__IGNORE__'] = 'If you received this email by mistake or don\'t wish to subscribe anymore, simply ignore this message.';
    // $emailData['__THANKS__'] = 'Thanks,';
    // $emailData['__FROM_NAME__'] = 'The Mailjet Team';
    return $emailData;
}
</code></pre>

##### Replace the email confirmation template with your own file
You need to have a php file with your custom template uploaded to your WordPress server. Then add the following code to your template functions.php file.
<pre><code>
/**
 * Replace default Mailjet template path with a your own
 * @param string $templatePath - the path of the default mailjet template
 * @return string
 */

add_filter( 'mailjet_confirmation_email_filename', 'useCustomConfirmationEmail' );
function useCustomConfirmationEmail($templatePath) {
    return './custom_subscription_path.php';
}
</code></pre>

##### Set your own Thank You page
You need to have a php file with your custom template uploaded to your WordPress server. Then add the following code to your template functions.php file.
<pre><code>
/**
 * Replace default Mailjet Thank You page template path with a your own
 * @param string $templatePath - the path of the default Mailjet Thank You page template
 * @return string
 */

add_filter( 'mailjet_thank_you_page_template', 'updateThankYouPagePath' );
function updateThankYouPagePath($templatePath) {
    return './custom_thank_you_page_path.php';
}
</code></pre>

##### Replace the widget form file
You need to have a php file with your custom template uploaded to your WordPress server. Then add the following code to your template functions.php file.
<pre><code>
/**
 * Replace default Mailjet widget form file with your own
 * @param string $templatePath - the path of the default Mailjet widget form file
 * @return string
 */

add_filter( 'mailjet_widget_form_filename', 'useMailjetCustomWidgetFormTemplate' );
function useMailjetCustomWidgetFormTemplate($templatePath) {
    return './custom_mailjet_widget_template.php';
}
</code></pre>

##### Use a template for transactinal emails
Mailjet allows you to take full advantage of the template language when using Mailjet's SMTP relay. For more info see [this guide](https://dev.mailjet.com/template-language/SMTP/). Add the following code to your template functions.php file.
<pre><code>
/**
 * Use SMTP headers to send emails with a specific transactional template
 * and leverage templating language to supply dynamic content to the template
 */
 
add_filter('wp_mail', 'my_wp_mail', 99);
function my_wp_mail($args) {
    /* Provide the transac template id */
    $args['headers'] .= 'X-MJ-TemplateID:123456' . "\r\n";
    /* Enable templating language */
    $args['headers'] .= 'X-MJ-TemplateLanguage:1' . "\r\n";
    /* Send template errors to this recipient */
    $args['headers'] .= 'X-MJ-TemplateErrorReporting:admin@your-site.com' . "\r\n";
    /* Send data to the template. The vars must be defined in the template in order to accept data */
    $args['headers'] .= 'X-MJ-Vars:' .
        json_encode(
            array(
                "var1" => $args['subject'], 
                "var2" => ($recipient->first_name) ? $recipient->first_name : false, 
                "var3" => $args['message']
            )
        )  
        . "\r\n";
    return $args;
}
</code></pre>

## For developers
Before pushing any new changes, make sure you run the following command. It will remove unneeded .git directories from vendors
<pre><code>
find vendor/ -type d -name ".git" -exec rm -rf {} \;
</code></pre>

## Screenshots

1. The initial setup wizard will guide you through the quick steps to get started
2. Access all features from the plugin dashboard
3. Create and send beautiful email campaigns
4. Configure a subscription widget to collect subscribers from your site
5. Activate and configure WooCommerce and Contact Form 7 integrations
6. Enable order notifications for WooCommerce
7. Configure abandoned cart notifications for WooCommerce

## Changelog

##### 5.2.5
* Ensured compatibility with WP version 5.6
* Removed Bootstrap dependency

##### 5.2.4
* Ensured compatibility with WP version 5.5 and previous versions
* Fixed issue with WooCommerce order status
* Security improvements

##### 5.2.3
* Reverted to 5.2.1

##### 5.2.2
* Ensure compatibility with WP version 5.5

##### 5.2.1
* Fixed compatibility with the Theme Customizer
* Added compatibility with custom roles
* Send subscription widget through Ajax
* Tweaks and optimizations

##### 5.2
* Added full WooCommerce integration
* Added widget shortcode support
* Fixed compatibility bug with Astra theme
* Fixed W3C validation issue with the widget code

##### 5.1.3
* Allow use of filter to replace the subscription confirmation email for Contact Form 7, WooCommerce and comment authors subscriptions

##### 5.1.2
* Allow use of TLS for ports 587 / 588
* Show the default thank you page when subscribing via Contact Form 7
* Translations fixes

##### 5.1.1
* Fixed a problem when saving the WooCommerce integration settings
* Fixed translations in the subscription confirmation email for Contact Form 7

##### 5.1.0
* New integration with Contact Form 7 has been added
* Added support for custom translation files
* Multiple widgets can be added on the same page
* Various bug fixes and improvements

##### 5.0.12
* User can upload custom language translation files
* Added Contact Form 7 integration

##### 5.0.11
* Fix confirmation link
* Create contact properties
* Change Connected to name
* Fix user access

##### 5.0.10
* Fix subscription confirmation URL for custom thank you page

##### 5.0.9
* Remove css and js public files to reduce public page speed loading
* WordPress widget is now working on wordpress version < 4.4
* Prevent fatal error on api timeout
* Add missing translations

##### 5.0.8
* Improve advanced options popup style
* Boolean type property is now checkbox
* Check requirements on activation
* Simplify onboarding UX
* Improve settings
* Advanced customisation link is always visible
* Added link to "Setup account" inside the Plugins section
* Improve compatibility with WP versions < 5.7

##### 5.0.7
* Fixed 'Thank you' page url when moving the blog across domains

##### 5.0.6
* Allow logged in WooCommerce customers to subscribe to the newsletter during checkout.
* Fixed css issues in the WP admin
* Small bugfixes and improvements

##### 5.0.5
* Fix subscription widget issues for multilanguage sites
* Fix fatal error for WooCommerce integration

##### 5.0.4
* Fix widget contact properties to be compatible with Polylang

##### 5.0.3
* Fix fatal error for php 5.5

##### 5.0.2
* Small bugfixes

##### 5.0.1
* Plugin redesign and major improvements
