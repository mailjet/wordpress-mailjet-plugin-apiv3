# AGENTS.md - AI Coding Agent Guide for Mailjet WordPress Plugin

## Project Overview
**Mailjet Email Newsletter Marketing** is a WordPress plugin (v6.1.7+) that integrates Mailjet's API (v3 via Guzzle) for email campaign management, transactional emails, and contact list synchronization. The plugin supports **WooCommerce**, **Contact Form 7**, and WordPress native integrations.

### Tech Stack
- **Language**: PHP 7.4+ (strict)
- **Architecture**: Object-oriented, PSR-4 autoload with namespacing
- **Package Manager**: Composer
- **External Deps**: `mailjet/mailjet-apiv3-php`, `sepia/po-parser`, `analog/analog` (logging)
- **WordPress Version**: 5.6+ (tested to 6.8.3)

---

## Critical Architecture Patterns

### 1. **Plugin Bootstrap & Lifecycle** (`wp-mailjet.php` → `src/includes/Mailjet.php`)
```php
// Entry point: wp-mailjet.php loads vendor autoloading, then instantiates Mailjet class
// Mailjet class constructor:
// 1. Loads dependencies (MailjetLoader)
// 2. Registers hooks for admin/public areas (define_admin_hooks, define_public_hooks)
// 3. Registers menu, settings, PHPMailer, and widget
// Activation: MailjetActivator checks PHP version, grants admin cap
// Deactivation: MailjetDeactivator clears cron jobs
```

**Key Files**: `wp-mailjet.php`, `src/includes/Mailjet.php`, `src/includes/MailjetActivator.php`

### 2. **Hook System & MailjetLoader** (`src/includes/MailjetLoader.php`)
The plugin uses a centralized **action/filter collector pattern** instead of scattered `add_action/add_filter` calls:
```php
// Pattern: Register hooks in constructor/init methods, then call $loader->run()
$loader->add_action('wp_mail_failed', $mail, 'wp_mail_failed_cb');
$loader->add_filter('wp_mail_from_name', $mail, 'wp_sender_name');
// Hooks execute in priority order via run() method at plugin init
```

**Implication**: All hook registration happens upfront via MailjetLoader—modify `src/includes/Mailjet.php` when adding new WordPress hooks, not scattered files.

### 3. **Namespacing & PSR-4 Autoload**
All classes use **`MailjetWp\MailjetPlugin\*`** namespace hierarchy matching directory structure:
```
"MailjetWp\\MailjetPlugin\\"                        → src/
"MailjetWp\\MailjetPlugin\\Includes\\"             → src/includes/
"MailjetWp\\MailjetPlugin\\Admin\\"                → src/admin/
"MailjetWp\\MailjetPlugin\\WidgetFormBuilder\\"    → src/widgetformbuilder/
```

**Import Pattern**: Always use `use MailjetWp\MailjetPlugin\Includes\Mailjet;` at top of file.

### 4. **API Integration & Singleton Pattern** (`src/includes/MailjetApi.php`)
```php
// Singleton pattern for API client to avoid multiple credential fetches
MailjetApi::getApiClient() // returns cached Client instance
MailjetApi::syncMailjetContact($listId, $contact, 'addforce')  // single contact
MailjetApi::syncMailjetContacts($listId, $contacts, 'addforce') // batch contacts
```

**Key Pattern**: Contact sync includes **contact properties** (custom fields):
```php
$contact = [
    'Email' => 'user@example.com',
    'Properties' => ['firstname' => 'John', 'role' => 'subscriber']
];
// Properties are synced to Mailjet; new properties auto-created via createMailjetContactProperty()
```

### 5. **Settings & Options Storage** (`src/includes/MailjetSettings.php`)
Settings are registered with WordPress Settings API, stored as options:
```php
// Registration in mailjet_settings_admin_init():
register_setting('mailjet_sending_settings_page', 'mailjet_enabled');
register_setting('mailjet_sending_settings_page', 'mailjet_from_email');

// Access pattern throughout codebase:
Mailjet::getOption('mailjet_enabled')     // retrieves from DB
update_option('mailjet_enabled', '1')      // WordPress function
```

**IMPORTANT**: New settings MUST be registered in `MailjetSettings::mailjet_settings_admin_init()` to be sanitized/stored by WP Settings API.

### 6. **Multi-Integration Pattern** (WooCommerce, Contact Form 7, Comments)
Each integration has:
- **Settings Page Class**: `src/includes/SettingsPages/{Integration}Settings.php`
- **Hooks Management**: Conditional hook registration in `Mailjet.php` based on `getOption()`
- **Data Sync**: Custom logic to transform local data to Mailjet contact format

Example (WooCommerce):
```php
// Mailjet.php constructor conditionally adds WooCommerce actions
if (self::getOption('activate_mailjet_woo_integration') == '1') {
    $this->addWoocommerceActions();  // calls WooCommerceSettings hooks
}

// WooCommerceSettings.php transforms order data to contact properties
// Hooks into: woocommerce_order_status_changed, checkout form, product data
```

---

## Developer Workflows

### Setup & Installation
```bash
# Install Composer dependencies (includes Mailjet API SDK)
composer install

# Install development tools if applicable
composer install --dev
```

### Adding a New Setting
1. **Register** the setting in `src/includes/MailjetSettings::mailjet_settings_admin_init()`:
   ```php
   register_setting('mailjet_custom_page', 'mailjet_new_setting');
   ```

2. **Retrieve** via: `Mailjet::getOption('mailjet_new_setting')`

3. **Persist** via: `update_option('mailjet_new_setting', $value)`

### Adding a New Integration
1. Create `src/includes/SettingsPages/{IntegrationName}Settings.php` extending settings pattern
2. Add conditional hook registration in `Mailjet.php::define_admin_hooks()` or constructor
3. Register settings in `MailjetSettings::mailjet_settings_admin_init()`
4. Create UI partial in `src/includes/SettingsPages/` directory (see IntegrationsSettings.php for reference)

### Testing API Calls
```php
// Use MailjetApi static methods (they handle auth from stored credentials)
try {
    $lists = MailjetApi::getMailjetContactLists();
    if ($lists === false) { /* connection error */ }
} catch (Exception $e) { /* missing credentials */ }

// For debugging, enable logging via Settings → Enable Logger
MailjetLogger::error('Debug message: ' . json_encode($data));
// Logs to: /wp-content/plugins/mailjet-for-wordpress/logs.txt
```

### Modifying SMTP/Mail Behavior
- Edit: `src/includes/MailjetMail.php::phpmailer_init_smtp()`
- Host: `in-v3.mailjet.com`
- Port/SSL configured via settings (default 587/TLS)
- User-agent header: `wordpress-{MAILJET_VERSION}`

---

## Project-Specific Conventions

### Contact Properties (Custom Fields)
Contact properties enable segmentation in Mailjet. Plugin auto-creates:
- **WordPress Users**: `firstname`, `lastname`, `user_role` (see `SubscriptionOptionsSettings::PROP_USER_*`)
- **WooCommerce**: Customer data synced with property names (configurable per integration)
- **Comment Authors**: Custom properties via filter

**Sync Pattern**:
```php
$contacts = [
    ['Email' => 'user@ex.com', 'Properties' => ['firstname' => 'John', 'role' => 'admin']],
    ['Email' => 'user2@ex.com', 'Properties' => ['firstname' => 'Jane', 'role' => 'subscriber']],
];
MailjetApi::syncMailjetContacts($listId, $contacts, 'addforce');
// 'addforce' = add/update existing contacts
// 'remove' = unsubscribe
// 'addnoforce' = add only if not exists
```

### Filter Hooks for Extension
Plugin provides strategic filters for customization:

```php
// Override template paths (widget, thank-you, confirmation email)
add_filter('mailjet_widget_form_filename', fn() => '/custom/widget.php');
add_filter('mailjet_thank_you_page_template', fn() => '/custom/thankyou.php');

// Customize synced contacts before batch upload
add_filter('mailjet_syncContactsToMailjetList_contacts', fn($contacts) => {
    // Transform $contacts before API call
    return $contacts;
});

// Customize confirmation email
add_filter('mailjet_confirmation_email_filename', fn() => '/custom/confirm.php');
```

### Widget & Form Builder
- **Single Subscription Widget**: `src/widgetformbuilder/WP_Mailjet_FormBuilder_Widget.php`
- Cached rendering via `wp_cache_set()` (see `widget()` method)
- Supports shortcode: `[mailjet_form_builder widget_id=1]`
- Form data from Mailjet Form Builder embedded as `form_builder_code` option

### Internationalization
- Text domain: `mailjet-for-wordpress`
- Language files: `languages/` (po/mo files for de_DE, fr_FR, es_ES, it_IT, en_GB, en_US)
- Dynamic translations: `Mailjeti18n::getTranslationsFromFile($locale, $string)` loads from po files

### Logging & Debugging
```php
// Only logs if mailjet_activate_logger option = 1
MailjetLogger::error('message');     // PHP-PSR-3 levels
MailjetLogger::debug('message');
// Output: wp-content/plugins/mailjet-for-wordpress/logs.txt
```

---

## Data Flow Patterns

### Contact Synchronization Pipeline
```
Source (WordPress users / WooCommerce / CF7 form)
    ↓
Transform to Mailjet format (add Properties)
    ↓
apply_filters('mailjet_syncContactsToMailjetList_contacts', $contacts)
    ↓
MailjetApi::syncMailjetContacts($listId, $contacts, 'addforce')
    ↓
HTTP POST to Mailjet API v3 (ContactslistManagemanycontacts resource)
    ↓
Update DB option 'mailjet_sync_list' to track state
```

### Email Sending Pipeline
```
wp_mail() called by WordPress/WooCommerce/etc
    ↓
PHPMailer::phpmailer_init hook (MailjetMail::phpmailer_init_smtp())
    ↓
Configure SMTP: host=in-v3.mailjet.com, auth with API key/secret, set From
    ↓
Send via Mailjet SMTP relay
    ↓
If fails: wp_mail_failed hook (MailjetMail::wp_mail_failed_cb())
```

### Settings Update Workflow
```
Admin submits form → WordPress Settings API sanitizes/validates
    ↓
Calls do_settings_sections() which calls registered setting callbacks
    ↓
Custom handlers in {Integration}Settings.php::*_post_handler() (e.g., IntegrationsSettings::integrations_post_handler())
    ↓
Validate, possibly trigger sync (e.g., all_customers_edata_sync), update options
    ↓
Redirect with $_GET['settings-updated'] for admin notice
```

---

## Critical Integration Points

### 1. **WooCommerce Hooks** (When enabled)
- `woocommerce_order_status_changed` → sync order e-commerce data
- `woocommerce_cheque_process_payment_order_status` → alternate payment status
- `woocommerce_add_to_cart`, `woocommerce_cart_emptied` → abandoned cart tracking (if enabled)
- Checkout/registration fields: opt-in checkboxes sync customers to list

**File**: `src/includes/SettingsPages/WooCommerceSettings.php`

### 2. **Contact Form 7 Integration** (When enabled)
- Hook: `wpcf7_mail_sent` → capture form submission
- Extract email + custom properties from form data
- Send confirmation email (customizable template)
- Sync contact to Mailjet list via GET params (confirmation link)

**File**: `src/includes/SettingsPages/ContactForm7Settings.php`

### 3. **WordPress Comment Authors**
- Checkbox option in comment form: subscribe to newsletter
- On comment posted: sync author to Mailjet if opted-in

**File**: `src/includes/SettingsPages/CommentAuthorsSettings.php`

### 4. **PHP Mailer / SMTP**
- ALL transactional emails (order notifications, password reset, etc.) route through Mailjet SMTP
- Credentials: API key (username) + API secret (password)
- From address: configurable setting or falls back to admin_email

---

## Common Tasks & Code Locations

| Task | File/Location |
|------|---------------|
| Add plugin menu item | `src/includes/MailjetMenu.php` |
| Create new settings page | `src/includes/SettingsPages/*.php` + `MailjetSettings.php` |
| Handle new WP hook | Add via `MailjetLoader` in `Mailjet.php` |
| Modify contact properties | `SubscriptionOptionsSettings::PROP_*` or integration-specific |
| Override template | Use filter (e.g., `mailjet_widget_form_filename`) |
| Add logging | `MailjetLogger::{level}('message')` |
| Work with contacts API | `MailjetApi::syncMailjetContact(s)()`, `getMailjetContactLists()`, `createMailjetContactList()` |
| Access plugin option | `Mailjet::getOption('option_name')` (from `wp_options` table) |

---

## Gotchas & Important Notes

1. **API Credentials Required**: All API calls check for stored credentials; missing → throws Exception. Always wrap in try-catch.

2. **Batch Operations**: Use `syncMailjetContacts()` (plural) for bulk; single contact use `syncMailjetContact()`.

3. **Property Sync**: Contact properties must be created BEFORE syncing contacts if they're new; plugin auto-creates via `createMailjetContactProperty()`.

4. **Settings Sanitization**: WP Settings API sanitizes input—don't re-validate in update_option calls.

5. **WooCommerce Conditional**: WooCommerce hooks only register if `activate_mailjet_woo_integration == '1'` (check `Mailjet.php::addWoocommerceActions()`).

6. **HTTPS/Proxy Support**: Plugin auto-detects HTTPS; if not detected, disables secure protocol. Respects WordPress `WP_PROXY_*` constants.

7. **Namespace Collision**: Plugin uses `MailjetWp\` prefix to avoid collisions with vendor Mailjet SDK (plain `Mailjet\` namespace).

8. **Caching**: Widget output cached via `wp_cache_set()` → flush on post save/delete/theme switch.

9. **Localization**: Translations loaded from po files via `Mailjeti18n::getTranslationsFromFile()`, not standard WP `__()` for some strings (enables without DB lookup).

---

## File Structure Reference

```
src/
├── includes/              # Core plugin logic
│   ├── Mailjet.php       # Main plugin class + hook orchestration
│   ├── MailjetApi.php    # Mailjet API wrapper (singleton client)
│   ├── MailjetMail.php   # PHPMailer SMTP bridge
│   ├── MailjetSettings.php  # WP Settings API registration
│   ├── MailjetLoader.php # Hook collector pattern
│   ├── MailjetLogger.php # Logging wrapper
│   ├── MailjetMenu.php   # Admin menu
│   ├── SettingsPages/    # Integration-specific settings classes
│   │   ├── WooCommerceSettings.php
│   │   ├── ContactForm7Settings.php
│   │   ├── CommentAuthorsSettings.php
│   │   ├── SubscriptionOptionsSettings.php
│   │   └── ... (14 settings page classes)
│   └── ...
├── admin/                # Admin UI assets & forms
│   ├── MailjetAdmin.php  # Enqueue styles/scripts
│   ├── css/, js/
│   └── partials/, images/
├── front/                # Public-facing assets
│   └── MailjetPublic.php
├── widgetformbuilder/    # Widget & form builder UI
│   └── WP_Mailjet_FormBuilder_Widget.php
├── templates/            # HTML templates (emails, forms)
│   ├── confirm-subscription-email.php
│   ├── thankyou.php
│   └── admin/, front/
└── mailjetIframe/        # (Legacy) Iframe API

composer.json             # PSR-4 autoload config, Mailjet API SDK dependency
wp-mailjet.php           # Plugin bootstrap entry point
```

