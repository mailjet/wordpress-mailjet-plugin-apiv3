=== Mailjet Email Marketing ===

- Contributors: Mailjet
- Tags: email, marketing, signup, newsletter, widget, smtp, woocommerce, contact form 7
- Requires at least: 4.4
- Tested up to: 6.6.1
- Stable tag: 6.0
- Requires PHP: 7.4
- License: GPLv2 or later
- License URI: http://www.gnu.org/licenses/gpl-2.0.html

Includes WooCommerce automated and order emails. Design, send and track engaging marketing and transactional emails from your WordPress admin.

== What's new ==

- Full **WooCommerce support** - use Mailjet's drag and drop email builder to create responsive *Order Notification* and *Abandoned cart reminder* emails and engage with your customers at the right moment. Add newsletter subscribers from the "Check out" and the "Thank you" pages. Sync order data to Mailjet and use our powerful *Segmentation* and *Automation* features to target specific customer groups.
- **Shortcode support** - add the subscription widget in any page or post using shortcode.
- Integration with **Contact Form 7** - add a "Subscribe to our newsletter" checkbox to any Contact Form 7 form and easily add subscribers to your contact lists

== Description ==
Mailjet's official plugin on WordPress gives you access to:
- **Easy Email Management:** Create and manage all of your marketing and transactional email campaigns directly from your WordPress admin.
- **Sign Up Form & Contact Lists Management:** Create and customize contact form widgets to allow your website visitors to subscribe to your newsletters. Add the subscription widget in any page or post using shortcode.
- **Automated Contact Synchronization** - 4 ways to build your contact lists:
  - Synchronize your *WordPress users*. The user role is added to Mailjet as a contact property, so you can filter and target marketing emails to the proper group. With ongoing synchronization, you never have to think about contact management again.
  - *Comment authors* can be added to a separate Mailjet contact list as they choose to subscribe while posting a comment on the blog.
  - Subscribe *WooCommerce customers* to your newsletter during checkout. Just enable the WooCommerce integration inside the plugin and you are ready to go.
  - Use the built in *Contact Form 7* integration and allow form submitters to subscribe to your newsletter.
- **Campaign Builder Tool:** Use our drag and drop email editor or HTML builder to create beautiful and engaging emails - directly from your WordPress admin.
- **Flexibility** - use filters to set your own subscription confirmation email template or texts inside the email, set your own thank you page, or widget form. See the FAQ for more details.
- **World Class Deliverability:** Hit your subscribers' inboxes every time with our global deliverability and routing infrastructure
- **Insight and analytics:** Access real-time statistics on your campaigns showing opens, clicks, geographies, average time to click and more to optimize your email performance.
- **Data Compliance:** Mailjet is GDPR compliant and ISO 27001 certified, meaning that it guarantees an optimal level of email data privacy and security.
- **International UI and Support:** Mailjet offers user interfaces, documentation and 24/7 customer support in 5 languages (English, French, German, Spanish and Italian).

== Installation ==

1. Log in as administrator in WordPress
2. Go to Plugins > Add New
3. Find "Mailjet Email Marketing" in the plugins directory and install it
   - or -  
   Click "Upload plugin" and upload the `mailjet-for-wordpress.zip` file from GitHub
4. Activate the Mailjet extension through the "Plugins" menu in WordPress

You must have cURL extension enabled. PHP 5.6 or later version is required.

## Frequently Asked Questions

= What is Mailjet? =
[Mailjet](http://www.mailjet.com?aff=wordpressmj) is an all-in-one solution to send, track and deliver both marketing and transactional emails. Its cloud-based infrastructure is unique and highly scalable with a proprietary technology that optimizes email deliverability. Mailjet can be accessed either via an easy-to-use online drag-and-drop interface or via APIs that allow developers to integrate its features within their online app or service, or its sophisticated SMTP relay.

= Why use Mailjet on WordPress? =
Because you don't have time to build a global leading email infrastructure and have a product or service to build and grow
Because you want to compose responsive Newsletters that engage your users and boost your traffic or revenue without having to code
Because you want the latest in email tracking technology that shows you which users open and click on your newsletters, when, on what device, on which links, etc.
Because you want to contact a Support Helpdesk that works when you do whether you're a morning person or a night owl.
Because you want to easily subscribe new users to your contact lists and not have to worry about keeping them in sync
Because you want to make sure your emails get delivered to the inbox!

= Do I need a Mailjet Account? =
Yes. You can [create one for free](https://app.mailjet.com/signup?aff=wordpressmj): it's easy and it only takes a few minutes.

= How to get started with this plugin? =
Once you have a Mailjet account, grab your [Mailjet API credentials](https://app.mailjet.com/account/apikeys) and activate the plugin. An installation wizard will guide you through.
For more help on setting up the Mailjet Plugin for WordPress, feel free to check out our [dedicated WordPress User Guide](https://www.mailjet.com/guides/wordpress-user-guide?aff=wordpressmj).

= How do I create a signup form or use the contact widget? =
Once your Mailjet plugin is installed and configured, click on "Appearance" in the left-side WordPress admin menu and then choose the "Widgets" section. Just drag the "Mailjet Subscription Widget" widget and drop it where you want it to appear (i.e. the sidebar). For more details, please visit the official help page [Adding Widgets](http://en.support.wordpress.com/widgets/#adding-widgets).

= How do I synchronize my contact lists? =
Synchronization is automatic, that's the beauty of this plugin! It doesn't matter whether your lists were updated on your WordPress interface or on your Mailjet account: they will always remain up-to-date on both sides.

= In which languages is this plugin available? =
The Mailjet Plugin is available in English, Spanish, French, German and Italian.
Need help? Our multilingual support team is here to answer your questions in any of these languages, any day of the week, at any time via our [online helpdesk](https://app.mailjet.com/support).


= Use filters to set your own Thank You page =
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

= Use filters to replace the widget form file =
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

= Use filters to apply a template for transactinal emails =
Mailjet allows you to take full advantage of the template language when using Mailjet's SMTP relay. For more info see [this guide](https://dev.mailjet.com/template-language/SMTP/). Add the following code to your template functions.php file.

/**
 * Use SMTP headers to send emails with a specific transactional template
 * and leverage templating language to supply dynamic content to the template
 */
<pre><code>
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

= For developers =
Before pushing any new changes, make sure you run the following command. It will remove unneeded .git directories from vendors =
<pre><code>
find vendor/ -type d -name ".git" -exec rm -rf {} \;
</code></pre>

== Screenshots ==

1. The initial setup wizard will guide you through the quick steps to get started
2. Access all features from the plugin dashboard
3. Configure a subscription widget to collect subscribers from your site
4. Activate and configure WooCommerce and Contact Form 7 integrations
5. Enable order notifications for WooCommerce
6. Configure abandoned cart notifications for WooCommerce

== Changelog ==

= 6.0 =
* Minimum PHP version required is 7.4
* Code cleanup and improvements
* In previous versions were fixed issues with infinite redirect, error during editing email templates in iframe. Warning issues according to SESSION

= 5.5.0 =
* Fixed bug with redirect problem during initial step
* Fixed links in the readme file and in the plugin

= 5.4.6 =
* Fixed bug with error during editing email templates in iframe.

= 5.4.5 =
* Fixed bug with activating Woocommerce email notification templates.

= 5.4.4 =
* Removed deprecated subscription widget.

= 5.4.3 =
* Fixed bug with infinite redirect when you go from plugin page to Mailjet settings page

= 5.4.2 =
* Fixed error in integration with Woocommerce. When you try to place an order
* Fixed changelog and readme file structure

= 5.4.1 =
* Added new translations for notice message
* Fix deactivate function to not throw an error

= 5.4.0 =
* New widget for Form Builder

== Upgrade Notice ==

= 5.4.2 =
* Fixed error in integration with Woocommerce. When you try to place an order
* Fixed changelog and readme file structure

= 5.4.1 =
* Added new translations for notice message
* Fix deactivate function to not throw an error

= 5.4.0 =
* New widget for Form Builder

= 5.3.7 =
* Added notice about updating plugin in the future (new Form Builder widget)

= 5.3.6 =
* Fixed error with Double opt-in and CF7 plugin
