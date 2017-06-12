=== Mailjet Email Newsletter Marketing ===

Contributors: mailjet
Tags: email, marketing, signup, newsletter, widget, smtp, mailjet
Requires at least: 3.3.0
Tested up to: 4.8
Stable tag: 4.1.19
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Use Mailjet to create, send and track beautiful and engaging marketing and transactional emails directly from within your WordPress admin. 


== Description ==

Mailjet’s official plugin on WordPress gives you  access to:

* Easy Email Management: 
Create and manage all of your marketing and transactional email campaigns directly from your WordPress Admin. 

* Sign Up Form & Contact Lists Management: 
Create and customize contact form widgets to allow your website visitors to subscribe to your newsletters.

* Automated Contact Synchronization: 
Automatically synchronize your WordPress contacts into their own separate Mailjet email lists. With ongoing synchronization, you never have to think about contact management again. 

* Campaign Builder Tool: 
Use our drag and drop email editor or HTML builder to create beautiful and engaging emails - directly from your WordPress Admin.

* World Class Deliverability: 
Hit your subscribers’ inboxes every time with our global deliverability and routing infrastructure

* Insight and analytics: 
Access real-time statistics on your campaigns showing opens, clicks, geographies, average time to click and more to optimize your email performance. 

* Data Compliance: 
Mailjet is EU-data compliant, meaning that it guarantees an optimal level of email data privacy. 

* International UI and Support: 
Mailjet offers user interfaces, documentation and 24/7 customer support in 4 languages (English, Spanish, French and German).

== Installation ==
 
1. Log in as administrator in Wordpress.
2. Go to Extensions > Add and send `mailjet-for-wordpress.zip`.
3. Activate the Mailjet extension through the 'Plugins' menu in WordPress.

You must have cURL extension enabled.

## Frequently Asked Questions

= What is Mailjet? =
[Mailjet](http://www.mailjet.com?aff=wordpressmj) is an all-in-one solution to send, track and deliver both marketing and transactional emails. Its cloud-based infrastructure is unique and highly scalable with a proprietary technology that optimizes email deliverability. Mailjet can be accessed either via an easy-to-use online drag-and-drop interface or via APIs that allow developers to integrate its features within their online app or service, or its sophisticated SMTP relay. 

= Why use Mailjet on Wordpress? =
Because you don’t have time to build a global leading email infrastructure and have a product or service to build and grow
Because you want to compose responsive Newsletters that engage your users and boost your traffic or revenue without having to code
Because you want the latest in email tracking technology that shows you which users open and click on your newsletters, when, on what device, on which links, etc.
Because you want to contact a Support Helpdesk that works when you do whether you’re a morning person or a night owl. 
Because you want to easily subscribe new users to your contact lists and not have to worry about keeping them in sync
Because you want to make sure your emails get delivered to the inbox!

= Do I need a Mailjet Account? =
Yes. You can [create one for free](https://app.mailjet.com/signup?aff=wordpressmj): it's easy and it only takes a few minutes.

= How to get started with this plugin? =
Once you have a Mailjet account, an installation Wizard will guide you through. You want to use Mailjet as an SMTP relay, so you will need to change these parameters in your Wordpress email configuration: username and password. These credentials are provided in your 'My Account > API Keys' section [HERE](https://app.mailjet.com/account/api_keys).
For more help on setting up the Mailjet Plugin for WordPress, feel free to check out our [dedicated WordPress User Guide](https://www.mailjet.com/guides/wordpress-user-guide?aff=wordpressmj). 

= How do I create a signup form or use the contact widget? =
Once your Mailjet plugin is installed, click on "Appearance" in the left-side menu and then choose the "Widgets" section. Just drag the "Subscribe to Our Newsletter" widget and drop it where you want it to appear (i.e. the sidebar). For more precisions, please visit the official help page [Adding Widgets](http://en.support.wordpress.com/widgets/#adding-widgets).

= How do  I synchronize my contact lists? =
Synchronization is automatic, that's the beauty of this plugin! It doesn't matter whether your lists were uploaded on your WordPress interface or on your Mailjet account: they will always remain up-to-date on both sides.

= In which languages is this plugin available? =
The Mailjet Plugin is available in English, Spanish, French and German. 
Need help? Our multilingual support team is here to answer your questions in any of these languages, any day of the week, at any time via our [online helpdesk](https://www.mailjet.com/support). 

== Screenshots ==

1. Simply change a few parameters to get started.
2. Manage your lists and contacts in no time!
3. Create and send beautiful email campaigns
4. Get instant insight on your campaign's performance with detailed statistics

== Changelog ==

= 4.1.19 =
* Updated text description, icon, screenshots and style

= 4.1.18 =
* Updated text description, icon, screenshots and style

= 4.1.17 =
* Updated Readme file

= 4.1.16 =
* Updated Readme file

= 4.1.15 =
* Updated screenshots
* Updated header image
* Updated Readme file

= 4.1.14 =
* Widget - disabled rewrite of subscription confirmation link

= 4.1.13 =
* Slight visual modifications - changed iframe left margin and width

= 4.1.12 =
* Updated 'Tested up to' version to include WordPress 4.7

= 4.1.11 =
* Updating widget CSS for better displaying on lower resolutions

= 4.1.10 =
* Add the link to the user guide & Support Ticket
* UX change in settings page

= 4.1.9 =
* Widget - css fix

= 4.1.8 =
* Improvement in Subscription widget - Adding an additional link

= 4.1.7 =
* Fix with additional fields on registration form

= 4.1.6 =
* System message update

= 4.1.5 =
* API call improvements

= 4.1.4 =
* Replaced jQuery .live() with .on()
* CSS conflict with a specific 3rd party plugin fix
* auto-subscribe improvement
* Updated 'Tested up to' version to include WordPress 4.4.1

= 4.1.3 =
* Widget - meta property fix
* Widget - on subscription, the code will check for valid input data types
* Widget - the input contact meta properties now appear correctly in the front end when the site is in language other than the 4 languages supported (EN, FR, DE, ES)

= 4.1.2 =
* Translations fixes
* Subscribe to contacts list with different scenarios - fix

= 4.1.1 =
* JavaScript fixes of the widget
* CSS fixes for jquery accordion ui plugin

= 4.1.0 =
* Subscription widget - added multilanguage support - EN, FR, ES, DE
* Display issue fix

= 4.0.1 =
* Updated 'Tested up to' version to include WordPress 4.4

= 4.0.0 =
* Subscription widget for v3 users can now include contact meta properties from the client e.g. first name, last name, age, location, industry etc, allowing up to 3 properties.

= 3.2.4 =
* Fixed sign up link translations

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
* The plugin is switched to v3 mailjet's users, and also the communication with mailjet is mainly executed with iframes

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
