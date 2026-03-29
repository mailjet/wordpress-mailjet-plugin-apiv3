# MailjetApi.php Security & Connection Safety Fixes

## Issues Found & Fixed

### ✅ Issue 1: Weak HTTPS Detection
**Problem**: Loose string comparison `$_SERVER['SERVER_PORT'] == 443` could fail with string port values
```php
// BEFORE (Line 70)
private static function isHttpsSupported(): bool
{
    return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' && $_SERVER['SERVER_PORT'] == 443;
}
```

**Fix**: 
- Use WordPress's native `is_ssl()` function (more reliable, handles proxy scenarios)
- Cast `SERVER_PORT` to integer for strict comparison
- Falls back gracefully if `is_ssl()` unavailable

```php
// AFTER
private static function isHttpsSupported(): bool
{
    if (\function_exists('is_ssl')) {
        return is_ssl();
    }
    $serverPort = isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : 0;
    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    return $isHttps && $serverPort === 443;
}
```

---

### ✅ Issue 2: No Proxy Validation
**Problem**: Proxy configuration applied without validating values, no error logging
```php
// BEFORE (Lines 62-68)
private static function configureProxy(Client $mjClient): void
{
    if (\defined('WP_PROXY_HOST') && \defined('WP_PROXY_PORT') && ...) {
        $mjClient->addRequestOption(\CURLOPT_PROXY, WP_PROXY_HOST . ':' . WP_PROXY_PORT);
        // No validation, no logging
    }
}
```

**Fix**:
- Validate all proxy constants are defined before using them
- Validate port is within valid range (0-65535)
- Add logging for successful/failed proxy configuration
- Wrap in try-catch for robustness

```php
// AFTER
private static function configureProxy(Client $mjClient): void
{
    $proxyRequired = [\defined('WP_PROXY_HOST'), \defined('WP_PROXY_PORT'), ...];
    if (\in_array(true, $proxyRequired, true) && !\in_array(false, $proxyRequired, true)) {
        try {
            $proxyHost = WP_PROXY_HOST ?? '';
            $proxyPort = WP_PROXY_PORT ?? 0;
            if (!empty($proxyHost) && $proxyPort > 0 && $proxyPort < 65536) {
                $mjClient->addRequestOption(\CURLOPT_PROXY, $proxyHost . ':' . $proxyPort);
                MailjetLogger::debug('Mailjet proxy configured: ' . $proxyHost . ':' . $proxyPort);
            } else {
                MailjetLogger::warning('Mailjet proxy configuration invalid: host=' . $proxyHost . ', port=' . $proxyPort);
            }
        } catch (Exception $e) {
            MailjetLogger::error('Mailjet proxy configuration failed: ' . $e->getMessage());
        }
    }
}
```

---

### ✅ Issue 3: No API Key Format Validation
**Problem**: API credentials accepted without format validation, opening door to invalid credentials silently passing
```php
// BEFORE (Lines 43-49)
public static function getApiClient(): Client
{
    $mailjetApikey = trim(Mailjet::getOption('mailjet_apikey'));
    $mailjetApiSecret = trim(Mailjet::getOption('mailjet_apisecret'));
    if (empty($mailjetApikey) || empty($mailjetApiSecret)) {
        throw new \Exception('...');
    }
    // No validation of format, just used directly
}
```

**Fix**:
- Validate API key format (alphanumeric, minimum length)
- Validate API secret format (alphanumeric, minimum length)
- Log validation failures for debugging
- Add connection timeout configuration (prevent hanging)

```php
// AFTER
public static function getApiClient(): Client
{
    // ... existing code ...
    if (empty($mailjetApikey) || empty($mailjetApiSecret)) {
        MailjetLogger::error('Mailjet API initialization failed: credentials missing');
        throw new \Exception('...');
    }
    
    // Validate API key format
    if (!\preg_match('/^[a-zA-Z0-9]{20,}$/', $mailjetApikey)) {
        MailjetLogger::error('Mailjet API key validation failed: invalid format');
        throw new \Exception('Mailjet API key format is invalid...');
    }
    
    // Validate API secret format
    if (!\preg_match('/^[a-zA-Z0-9]{20,}$/', $mailjetApiSecret)) {
        MailjetLogger::error('Mailjet API secret validation failed: invalid format');
        throw new \Exception('Mailjet API secret format is invalid...');
    }
    
    $mjClient->addRequestOption(\CURLOPT_TIMEOUT, 30);           // Prevent hanging
    $mjClient->addRequestOption(\CURLOPT_CONNECTTIMEOUT, 10);   // Connection timeout
}
```

---

### ✅ Issue 4: Silent Exception Handling (No Logging)
**Problem**: Many methods catch exceptions without logging, making debugging difficult
```php
// BEFORE
public static function getMailjetContactLists()
{
    try {
        $mjApiClient = self::getApiClient();
    } catch (Exception $e) {
        return false;  // Silent failure
    }
    try {
        $response = $mjApiClient->get(...);
    } catch (ConnectException $e) {
        return \false;  // Silent failure
    }
}
```

**Fix**: 
- Add logging for all caught exceptions
- Distinguish between ConnectException (network) and RequestException (API)
- Create helper method `handleApiException()` for consistent error logging

```php
// AFTER
private static function handleApiException(Exception $e, string $methodName): void
{
    $errorMsg = $e->getMessage();
    if ($e instanceof ConnectException) {
        MailjetLogger::error("Mailjet API connection error ($methodName): $errorMsg");
    } elseif ($e instanceof RequestException) {
        MailjetLogger::error("Mailjet API request error ($methodName): $errorMsg");
    } else {
        MailjetLogger::error("Mailjet API error ($methodName): $errorMsg");
    }
}

public static function getMailjetContactLists()
{
    try {
        $mjApiClient = self::getApiClient();
    } catch (Exception $e) {
        MailjetLogger::error('Mailjet contact lists fetch failed: ' . $e->getMessage());
        return false;
    }
    try {
        $response = $mjApiClient->get(...);
    } catch (ConnectException $e) {
        MailjetLogger::error('Mailjet API connection error (getMailjetContactLists): ' . $e->getMessage());
        return \false;
    } catch (RequestException $e) {
        MailjetLogger::error('Mailjet API request error (getMailjetContactLists): ' . $e->getMessage());
        return \false;
    }
}
```

---

### ✅ Issue 5: No Input Validation for Contact Sync
**Problem**: `syncMailjetContact(s)` methods don't validate input parameters or action values
```php
// BEFORE
public static function syncMailjetContacts($contactListId, $contacts, $action = 'addforce')
{
    try {
        $mjApiClient = self::getApiClient();
    } catch (Exception $e) {
        return \false;
    }
    // Just send whatever was provided - no validation
}
```

**Fix**:
- Validate `$contactListId` is not empty
- Validate `$contacts` is array and not empty
- Validate `$action` is within allowed values: `addforce`, `addnoforce`, `remove`, `unsub`
- Log all validation failures

```php
// AFTER
public static function syncMailjetContacts($contactListId, $contacts, $action = 'addforce')
{
    // Validate inputs
    if (empty($contactListId) || !is_array($contacts) || empty($contacts)) {
        MailjetLogger::warning('syncMailjetContacts: invalid input (listId=' . var_export($contactListId, true) . ', contactsCount=' . (is_array($contacts) ? count($contacts) : 'N/A') . ')');
        return \false;
    }
    
    // Validate action parameter
    $validActions = ['addforce', 'addnoforce', 'remove', 'unsub'];
    if (!\in_array($action, $validActions)) {
        MailjetLogger::error('syncMailjetContacts: invalid action parameter: ' . $action);
        return \false;
    }
    
    // ... rest of method
}
```

---

### ✅ Issue 6: Incomplete Exception Handling in Looping
**Problem**: `getSubscribersFromList()` catches `ConnectException` but not `RequestException` in pagination loop
```php
// BEFORE
while ($response->getCount() >= $limit) {
    try {
        $response = $mjApiClient->get(...);
    } catch (ConnectException $e) {
        return \false;  // Only catches ConnectException
    }
}
```

**Fix**:
- Catch both `ConnectException` and `RequestException` separately
- Log specific error type and offset for debugging
- Add logging for API-level failures

```php
// AFTER
while ($response->getCount() >= $limit) {
    try {
        $response = $mjApiClient->get(...);
    } catch (ConnectException $e) {
        MailjetLogger::error('Mailjet API connection error (getSubscribersFromList offset=' . $offset . '): ' . $e->getMessage());
        return \false;
    } catch (RequestException $e) {
        MailjetLogger::error('Mailjet API request error (getSubscribersFromList offset=' . $offset . '): ' . $e->getMessage());
        return \false;
    }
    if (!$response->success()) {
        MailjetLogger::error('Mailjet API error: failed to fetch subscribers (listId=' . $contactListId . ', offset=' . $offset . ')');
        return \false;
    }
}
```

---

## Summary of Safety Improvements

| Issue | Before | After |
|-------|--------|-------|
| HTTPS Detection | Fragile string comparison | WordPress native `is_ssl()` + type-safe fallback |
| Proxy Validation | No validation or logging | Full validation, error handling, logging |
| API Key Validation | None | Format validation (alphanumeric, min length) |
| Timeout Configuration | None set | 30s request + 10s connect timeouts |
| Exception Logging | Silent failures | Comprehensive logging with context |
| Input Validation | None | Parameter validation + action whitelist |
| Error Handling | Partial catch blocks | Both ConnectException & RequestException caught |

---

## Debugging Tips

Enable logging to see detailed API errors:
```php
// WordPress admin → Mailjet Settings → Enable Logger
// Logs written to: /wp-content/plugins/mailjet-for-wordpress/logs.txt
```

**Common errors to watch for:**
- `Mailjet API connection error`: Network/firewall issue
- `Mailjet API key validation failed`: Corrupted/invalid credentials stored
- `Mailjet proxy configuration invalid`: WP_PROXY_* constants malformed
- `invalid action parameter`: Wrong sync action passed (check for typos)

---

## Affected Methods (Enhanced)
1. `getApiClient()` - API credential validation & timeout
2. `configureProxy()` - Proxy validation & error logging
3. `isHttpsSupported()` - WordPress-aware HTTPS detection
4. `isValidAPICredentials()` - Comprehensive exception handling
5. `getMailjetContactLists()` - Connection & request error logging
6. `getSubscribersFromList()` - Detailed pagination error logging
7. `syncMailjetContact()` - Input & action validation
8. `syncMailjetContacts()` - Input & action validation

**Plus**: New `handleApiException()` helper method for consistent error handling

