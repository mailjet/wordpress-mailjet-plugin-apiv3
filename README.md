=== Mailjet Email Newsletter Marketing ===

Contributors: Mailjet
Tags: email, marketing, signup, newsletter, widget, smtp, mailjet
Requires at least: 3.3.0
Tested up to: 5.0.3
Stable tag: 5.0.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Use Mailjet to create, send and track beautiful and engaging marketing and transactional emails directly from within your WordPress admin.

== What's new ==
* The all new V5 Mailjet plugin for Wordpress has been completely redesigned and includes many new features.
* Improved plugin settings for simplified configuration
* Redesigned subscription widget for easier setup and more options - mandatory, optional or hidden form fields, custom subscription confirmation page, and more.
* Integration with WooCommerce is included - customers can subscribe to your newsletter during checkout. More integrations are coming soon.
* More flexibility - use filters to set your own subscription confirmation email template or texts inside the email, set your own thank you page, or widget form.

== Description ==

Mailjet's official plugin on WordPress gives you access to:

* Easy Email Management:
Create and manage all of your marketing and transactional email campaigns directly from your WordPress admin.

* Sign Up Form & Contact Lists Management:
Create and customize contact form widgets to allow your website visitors to subscribe to your newsletters.

* Automated Contact Synchronization - 3 ways to build your contact lists:

Synchronize your WordPress contacts. The user role is added to Mailjet as a contact property, so you can filter and target marketing emails to the proper group. With ongoing synchronization, you never have to think about contact management again.

Comment authors can be added to a separate Mailjet contact list as they choose to subscribe while posting a comment on the blog.

A new cool option is to subscribe WooCommerce customers to your newsletter during checkout. Just enable the WooCommerce integration inside the plugin and you are ready to go.

* Campaign Builder Tool:
Use our drag and drop email editor or HTML builder to create beautiful and engaging emails - directly from your WordPress admin.

* World Class Deliverability:
Hit your subscribers' inboxes every time with our global deliverability and routing infrastructure

* Insight and analytics:
Access real-time statistics on your campaigns showing opens, clicks, geographies, average time to click and more to optimize your email performance.

* Data Compliance:
Mailjet is GDPR compliant and ISO 27001 certified, meaning that it guarantees an optimal level of email data privacy and security.

* International UI and Support:
Mailjet offers user interfaces, documentation and 24/7 customer support in 5 languages (English, French, German, Spanish and Italian).

== Installation ==

1. Log in as administrator in Wordpress.
2. Go to Plugins > Add New
3. Find "Mailjet Email Marketing" in the plugins directory and install it
- or -
Click "Upload plugin" and upload the `mailjet-for-wordpress.zip` file from GitHub
4. Activate the Mailjet extension through the "Plugins" menu in WordPress.

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
Once you have a Mailjet account, an installation Wizard will guide you through.
In case you want to use Mailjet as an SMTP relay you will need to change these parameters in your WordPress email configuration: username and password. These credentials are provided in your Mailjet Account > API Keys section [HERE](https://app.mailjet.com/account/api_keys).
For more help on setting up the Mailjet Plugin for WordPress, feel free to check out our [dedicated WordPress User Guide](https://www.mailjet.com/guides/wordpress-user-guide?aff=wordpressmj).

= How do I create a signup form or use the contact widget? =
Once your Mailjet plugin is installed and configured, click on "Appearance" in the left-side WordPress admin menu and then choose the "Widgets" section. Just drag the "Mailjet Subscription Widget" widget and drop it where you want it to appear (i.e. the sidebar). For more details, please visit the official help page [Adding Widgets](http://en.support.wordpress.com/widgets/#adding-widgets).

= How do I synchronize my contact lists? =
Synchronization is automatic, that's the beauty of this plugin! It doesn't matter whether your lists were updated on your WordPress interface or on your Mailjet account: they will always remain up-to-date on both sides.

= In which languages is this plugin available? =
The Mailjet Plugin is available in English, Spanish, French, German and Italian.
Need help? Our multilingual support team is here to answer your questions in any of these languages, any day of the week, at any time via our [online helpdesk](https://app.mailjet.com/support).

= How to use filters to customize the subscription confirmation email template =
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

= How to use filters to replace the email confirmation template with my own file =
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

= How to use filters to set your own Thank You page =
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

= How to use filters to replace the widget form file =
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

= For developers - before pushing any new changes, make sure you run the following command. It will remove unneeded .git direcotries from vendors =
<pre><code>
find vendor/ -type d -name ".git" -exec rm -rf {} \;
</code></pre>

== Screenshots ==

1. The initial setup wizard will guide you through the quick steps to get started
2. Access all features from the plugin dashboard
3. Create and send beautiful email campaigns
4. Configure a subscription widget to collect subscribers from your site

== Changelog ==

= 5.0.8 =
* Improve advanced options popup style
* Boolean type property is now checkbox
* Check requirements on activation
* Simplify onboarding UX
* Improve settings
* Advanced customisation link is always visible
* Added link to "Setup account" inside the Plugins section

= 5.0.7 =
* Fix 'thank you' page url when moving the blog across domains

= 5.0.6 =
* Allow logged in WooCommerce customers to subscribe to the newsletter during checkout.
* Fixed css issues in the WP admin
* Small bugfixes and improvements


= 5.0.5 =
* Fix subscription widget issues for multilanguage sites
* Fix fatal error for WooCommerce integration

= 5.0.4 =
* Fix widget contact properties to be compatible with polylang

= 5.0.3 =
* Fix fatal error for php 5.5

= 5.0.2 =
* Fix small bugfixes

= 5.0.1 =
* Plugin redesign and major improvements
