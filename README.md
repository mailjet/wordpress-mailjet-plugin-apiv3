# Mailjet Email Newsletter Marketing

- Contributors: Mailjet
- Tags: email, marketing, signup, newsletter, widget, smtp, woocommerce, contact form 7
- Requires at least: 5.6
- Tested up to: 7.1
- Stable tag: 6.2.6
- Requires PHP: 7.4
- License: GPLv2 or later
- License URI: http://www.gnu.org/licenses/gpl-2.0.html

Includes WooCommerce automated and order emails. Design, send and track engaging marketing and transactional emails from your WordPress admin.

## What's new
* Full **WooCommerce support** - use Mailjet's drag and drop email builder to create responsive *Order Notification* and *Abandoned cart reminder* emails and engage with your customers at the right moment. Add newsletter subscribers from the "Check out" and the "Thank you" pages. Sync order data to Mailjet and use our powerful *Segmentation* and *Automation* features to target specific customer groups.
* **Shortcode support** - add the subscription widget in any page or post using shortcode.
* Integration with **Contact Form 7** - add a "Subscribe to our newsletter" checkbox to any Contact Form 7 form and easily add subscribers to your contact lists
* Support new widget for Form Builder

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
[Mailjet](https://www.mailjet.com?aff=wordpressmj) is an all-in-one solution to send, track and deliver both marketing and transactional emails. Its cloud-based infrastructure is unique and highly scalable with a proprietary technology that optimizes email deliverability. Mailjet can be accessed either via an easy-to-use online drag-and-drop interface or via APIs that allow developers to integrate its features within their online app or service, or its sophisticated SMTP relay.

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
Once you have a Mailjet account, grab your [Mailjet API credentials](https://app.mailjet.com/account/apikeys) and activate the plugin. An installation wizard will guide you through. 
For more help on setting up the Mailjet Plugin for WordPress, feel free to check out our [dedicated WordPress User Guide](https://www.mailjet.com/guides/wordpress-user-guide?aff=wordpressmj).

##### How do I create a signup form or use the contact widget?
Once your Mailjet plugin is installed and configured, click on "Appearance" in the left-side WordPress admin menu and then choose the "Widgets" section. Drag the "Mailjet Subscription Widget" widget and drop it where you want it to appear (i.e. the sidebar). For more details, please visit the official help page [Adding Widgets](https://wordpress.com/support/widgets/#adding-widgets).

##### In which languages is this plugin available?
The Mailjet Plugin is available in English, Spanish, French, German and Italian.
Need help? Our multilingual support team is here to answer your questions in any of these languages, any day of the week, at any time via our [online helpdesk](https://app.mailjet.com/support).

#### Using filters ####

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

## Hook for overriding the "From" details

You can use the wp_mail_from_name filter in WordPress to dynamically set the "From Name" based on the form submission data. Below is a workaround function that checks if the email is being sent by Ninja Forms and sets the "From Name" to the name of the form submitter.
Explanation:
The function hooks into the wp_mail_from_name filter.
It checks if the email is being sent by Ninja Forms by inspecting the $phpmailer global object.
If the email is from Ninja Forms, it retrieves the submitter's name from the email headers or content.
It sets the "From Name" dynamically.


```php
add_filter('wp_mail_from_name', function ($from_name) {
    global $phpmailer;

    // Check if PHPMailer is initialized
    if (!isset($phpmailer) || !is_object($phpmailer)) {
        return $from_name;
    }

    // Check if the email is from Ninja Forms
    if (isset($phpmailer->Subject) && strpos($phpmailer->Subject, 'Ninja Forms') !== false) {
        // Extract the submitter's name from the email headers or body
        if (preg_match('/Name:\s*(.+)/i', $phpmailer->Body, $matches)) {
            $from_name = trim($matches[1]);
        }
    }

    return $from_name;
});
```

## Screenshots

1. The initial setup wizard will guide you through the quick steps to get started
2. Access all features from the plugin dashboard
3. Configure a subscription widget to collect subscribers from your site
4. Activate and configure WooCommerce and Contact Form 7 integrations
5. Enable order notifications for WooCommerce
6. Configure abandoned cart notifications for WooCommerce

## Changelog

##### 6.2.6
- Security: the `resync_mailjet` and `get_contact_lists_menu` AJAX actions now require a nonce (`check_ajax_referer`) and the `mailjet_plugin_access` capability; they answer HTTP 403 otherwise. Previously any logged-in user (e.g. a Subscriber) could read the sync list ID, WP user count and Mailjet contact lists, or force a full resync of all WP users, and the resync was also exposed to CSRF

##### 6.2.5
- Security: the Mailjet API client always uses HTTPS. Removed the fallback that switched API calls (including credentials) to plain HTTP whenever the WordPress site itself was not served over HTTPS; the legacy iframe client's `secure(false)` is now ignored
- Switched remaining `http://` links (Google Fonts stylesheet, plugin Author URI, readme links) to HTTPS

##### 6.2.4
- Security (CVE-2026-39791): the `mj_ajax_subscribe` AJAX action no longer exposes customer data to unauthenticated visitors. It now requires a nonce and the WooCommerce order key (checked with `hash_equals`), answers a missing order and a wrong key identically, and no longer echoes the billing email in its response
- Fixed the thank-you page "Subscribe now" button sending no request because the order ID attribute contained surrounding whitespace

##### 6.2.3
- Fixed WooCommerce abandoned cart and order-notification emails failing with HTTP 400: `getFormattedEmailData()` now builds a valid Send API v3.1 message (`From`/`To`/`TemplateID`/`TemplateLanguage`/`Variables`) instead of a legacy v3 body
- Fixed abandoned cart cron retrying failed sends every minute forever: each cart is now emailed at most once, the batch is capped per run, and the run stops when the API returns HTTP 429
- Abandoned cart cron now skips deleted products instead of fatally erroring, validates the recipient before creating a tracking row, caches the template lookup, and no-ops when the feature is disabled
- Declared WooCommerce High-Performance Order Storage (HPOS) compatibility and moved the remaining `get_post_meta()`/`update_post_meta()` order calls onto the WooCommerce order API (`$order->get_meta()` / `$order->update_meta_data()`)
- Text domain now loads on `init` instead of `plugins_loaded` to avoid the WordPress 6.7+ "translation triggered too early" notice
- Removed deprecated `utf8_decode()` usage (removed in PHP 9) from the legacy iframe client
- Synced version metadata across `wp-mailjet.php`, `MAILJET_VERSION`, `readme.txt` and `README.md`; tested up to WordPress 7.1
- Corrected the activation PHP-version notice to state the real requirement (7.4)

##### 6.2.0
- Enhanced API security with comprehensive validation and error handling
- Added API key and secret format validation (regex pattern matching)
- Improved HTTPS detection using WordPress native `is_ssl()` function
- Added proxy configuration validation with port range checking (0-65535)
- Implemented connection timeouts (30s request, 10s connect) to prevent hanging
- Added comprehensive exception logging for ConnectException and RequestException
- Enhanced contact sync methods with input validation and action parameter whitelist
- Added detailed error logging with context information for debugging
- Fixed GitHub Actions workflow with proper YAML structure and configuration
- Added automated PR checks: PHPCS (code quality) and PHP Security Checker (dependencies)
- Added AGENTS.md documentation guide for AI coding agents
- Improved error messages and logging throughout API integration

##### 6.1.6
- Fixing code base
- Fixed possible warning in the code
- Added code example of From name workaround in the readme file

##### 6.1.5
- Fixed issue with wrong permission check on integration page

##### 6.1.4
- Fix issues in the input fields. Tested with new WP version

##### 6.1.2
- Fix possible error when code switch websites and could leave website in a broken state

##### 6.1
 - Added support Multisite settings across all sites. Now, you can use plugin settings from the main site on all your subdomains.

##### 6.1.1
- Fix PHP Fatal error:  Uncaught Error: Class 'MailjetWp\\MailjetPlugin\\Front\\Mailjet

##### 6.0.1
* Removed old widget filter function `mailjet_subscription_widget_email_params`. It was deprecated and unsupported since we have Form Builder widget

##### 6.0
* Minimum PHP version required is 7.4
* Code cleanup and improvements
* In previous versions were fixed issues with infinite redirect, error during editing email templates in iframe. Warning issues according to SESSION

##### 5.5.0
* Fixed bug with redirect problem during initial step
* Fixed links in the readme file and in the plugin

##### 5.4.6
* Fixed bug with error during editing email templates in iframe.

##### 5.4.5
* Fixed bug with activating Woocommerce email notification templates.

* ##### 5.4.4
* Removed deprecated subscription widget.

##### 5.4.3
* Fixed bug with infinite redirect when you go from plugin page to Mailjet settings page

##### 5.4.2
* Fixed error in integration with Woocommerce. When you try to place an order
* Fixed changelog and readme file structure

##### 5.4.1
* Added new translations for notice message
* Fix deactivate function to not throw an error

##### 5.4.0
* New widget for Form Builder

##### 5.3.7
* Added notice about updating plugin in the future (new Form Builder widget)

##### 5.3.6
* Fixed error with Double opt-in and CF7 plugin 

