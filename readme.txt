=== Mailjet for Wordpress ===

Contributors: mailjet
Tags: email, marketing, signup, newsletter, widget, smtp, mailjet
Requires at least: 3.2.1
Tested up to: 4.1.0
Stable tag: 3.2.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Use Mailjet to create and send personalised Newsletters, sync contacts and gather advanced analytics.

== Description ==

Mailjet's official plugin for WordPress will allow you to:

* Send both transactional and marketing emails from your Wordpress site;
* Reconfigure your wp_mail() function to use Mailjet's SMTP instead of mail() for enhanced deliverability and tracking;
* To easily create contact lists and manage personalisation variables;
* Have the insight you need from a real-time dashboard showing opens, clicks, geographies, average time to click, user agents, etc;
* Easily create and send marketing campaigns using drag and drop Newsletter builder or selecting from our template library;
* Provide a signup widget, so your visitors can sign up to your Mailjet mailing lists.


** Secure & real-time SMTP relay for all your emails **
- A lot of features and plugins from your WordPress site send email notifications. All these messages are very important for your users, as well as for your business. Therefore, you want to track success and ensure a successful delivery.

Our plugin simply extends the WordPress wp_mail() function, to use Mailjet's SMTP instead. This will improve your deliverability. You will also get live and in-depth statistics to track and optimize in real time. Making the choice of Mailjet is the right solution for your [transactional emails](http://www.mailjet.com/features/transactional-email.html) , [bulk emails](http://www.mailjet.com/features/bulk-email.html) and [newsletter emails](https://www.mailjet.com/features/newsletter-service.html).

** Sign up form & contact lists Management **
-  Another great feature of this plugin is the sign up form Widget. It allows your site visitors to join your Mailjet lists. You can create contact lists directly from the plugin dashboard. The Widget will let you add forms to any post or any page.

** Mailjet's latest generation v3 iframes **
-  Last but not least, the plugin features Mailjet's latest v3 iframes to manage contacts, create drag-and-drop marketing campaigns and view in depth sending statistics directly from within the plug-in.

** Installing the Plugin **
-  Enable Mailjet's Plugin like you would do for any other Wordpress plugin. Enter your Mailjet credentials and refer to the FAQs for any other information. If you don't have a Mailjet account yet, [signup now](http://www.mailjet.com) for free!

*** SECURITY UPDATE *** if you connect your Mailjet account and get a "php_network_getaddresses: getaddrinfo failed" or if you try to open "contacts", "statistics"  and the login form appears AND your Mailjet account was created prior to August 2014, please go to [Account API keys](https://www.mailjet.com/account/api_keys) to regenerate a new Secret Key for the plug-in.

== Installation ==
 
1. Log in as administrator in Wordpress.
2. Go to Extensions > Add and send `mailjet-for-wordpress.zip`.
3. Activate the Mailjet extension through the 'Plugins' menu in WordPress.

You must have cURL extension enabled.

*** SECURITY UPDATE *** if you connect your Mailjet account and get a "php_network_getaddresses: getaddrinfo failed" or if you try to open "contacts", "statistics"  and the login form appears AND your Mailjet account was created prior to August 2014, please go to [Account API keys](https://www.mailjet.com/account/api_keys) to regenerate a new Secret Key for the plug-in.



## Frequently Asked Questions


= What is Mailjet? =
[Mailjet](http://www.mailjet.com) is an all-in-one solution to send, track and deliver both marketing and transactional emails. Its Cloud-Based infrastructure is unique and highly scalable. A proprietary technology optimizes the sending and the deliverability of your messages.

= Why use Mailjet as an SMTP relay for Wordpress? =
1 in 4 legitimate email gets lost or ends up a the spam folder. By Sending your email through Mailjet's SMTP relay, you will avoid this and make sure that your recipients receive your messages in real time, straight into their inbox. You will also be able to track the delivery (opens, bounces, etc.) as well as the activity of your emails (clicks, opens, etc.). On top of that, tools such as our Newsletter editor will let you create and send a beautiful marketing campaign in only a few minutes. All this added value comes with no engagement and very low prices.

= Do I need a Mailjet Account? =
Yes. You can create one for free: it's easy and it only takes a few minutes.

= How to get started with this plugin? =
Once you have a Mailjet account, an installation Wizard will guide you through. You want to use Mailjet as an SMTP relay, so you will need to change these parameters in your Wordpress email configuration: username and password. These credentials are provided in your 'My Account > API Keys' section [HERE](https://app.mailjet.com/account/api_keys)

= How do I get synchronize my lists? =
Synchronization is automatic, that's the beauty of this plugin! It doesn't matter whether your lists were uploaded on your Wordpress interface or on your Mailjet account: they will always remain up-to-date on both sides.

= In which languages is this plugin available? =
This plugin is currently available in English, French, Spanish and German. Support is also provided in each of these languages at via our [online Helpdesk](https://www.mailjet.com/support/ticket)

= How do I create a signup form? =
Once your Mailjet plugin is installed, click on "Appearance" in the left-side menu  and then choose the "Widgets" section. Just drag  the "Subscribe to Our Newsletter" widget and drop it where you want it to appear (i.e. the sidebar). For more precisions, please visit the official help page [Adding Widgets](http://en.support.wordpress.com/widgets/#adding-widgets).


== Screenshots ==

1. Simply change a few parameters to get started.
2. Manage your lists and contacts in no time!
3. Create and send beautiful email campaigns
4. View detailed statistics about your account


== Changelog ==

= 3.2.2 =
* Added new iFrame param - sp=display - to display sending policy block

= 3.2.1 =
* Removed Reply-To header when using PHPMailer

= 3.1.21 =
* Readme file update

= 3.1.20 =
* Added USER AGENT tracking on every curl request

= 3.1.19 =
* Widget button translation fix

= 3.1.18 =
* Servers with PHP version 5.3 would be able to check if headers for ReplyTo already exist and add the Mailjet headers only if no ReplyTo headers are set.

= 3.1.17=
* When we create a TOKEN we also send SentData containing plugin name

= 3.1.16=
* Translation of the iframe and also some small corrections are executed for the translations from i18n folder;

= 3.1.15=
* All strings are translated in german, french and spanish.

= 3.1.14=
* Changed checking for correct sender.

= 3.1.13=
* Modification of how to detect v1 and v3 users.

= 3.1.12=
* Part of the translations are translated.

= 3.1.11=
* We display more human readable messages for those cases when we enter wrong API/Secret Key and also our "from email" does not match with any of the sender part of our Mailjet's account

= 3.1.10=
* Rename function my_save_extra_profile_fields to mailjet_my_save_extra_profile_fields

= 3.1.9=
* Bug fix related to the compatibility of the plugin for v1 and v3 users

= 3.1.8=
* Stop scripts from being output unnecessarily when we load the widget on the front-end.

= 3.1.7=
* The user can set the port from the settings page.

= 3.1.6=
* The administrator of the website is able to give access to the plugin for other user roles.

= 3.1.5=
* MJ_HOST and MJ_MAILER variables are moved to the api strategy patter class as public properties of the class. From now on, they are
accessible from there.

= 3.1.4=
* Add a confirmation message  when an user activates his/her contact widget on his/her Wordpress admin
* In the admin panel of WordPress, when a user try to add a "subscribe to our newsletter widget"  we have add a validation if he/she has chosen a contact list

= 3.1.3=
* The position of the main Mailjet's menu takes a default value instead of value 101
* Removed AKID from APIv3 calls from the plugin
* Prevent plugin from hiding other plugin's page #10
* README update

= 3.1.2 =
* Updated readme

= 3.1.1 =
* Tag bug fix for V1 & V3 compatible plugin

= 3.1.0 =
* Supports V1 and V3 Mailjet's users, Add use tracking on the WordPress plugin, Fix IsActive parameter for token creation

= 3.0.3 =
* Add tracking of signups on the WordPress plugin, Fix IsActive parameter for token creation

= 3.0.2 =
* Fix MailJet host

= 3.0.1 =
* Bug fix on connecting contact to a list

= 3.0.0 =
* The pluging is switched to v3 mailjet's users, and also the communication with mailjet is mainly executed with iframes

= 1.2.8 =
* Added cURL warning and missing translations

= 1.2.7 =
* Bug fix on ssl option and widget constructor, code cleaning

= 1.2.6 =
* Bug fix

= 1.2.5 =
* Add ability to autosubscribe newly registered users to a specific list

= 1.2.3 =
* Add ports 80 and 588 to work around some hosts limitations

= 1.2.2 =
* Added Ability to change widget button text

= 1.2.1 =
* Added campaigns and statistics management

= 1.1.5 =
* Added: HTTP Port Configuration

= 1.1.3 =
* Bugfix: Correct ajax request for WordPress 3.2.1
* Bugfix: Correct widget creation

= 1.1.1 =
* added readme and translations files

= 1.1 =
* Bug fix: compatibility with WordPress 3.4
* Add support for list edition and creation
* Add signup form to list in native WordPress widget

= 1.0.1 =
* Bug fix on install.

= 1.0=
* First stable release.
