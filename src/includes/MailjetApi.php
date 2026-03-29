<?php

namespace MailjetWp\MailjetPlugin\Includes;

use Exception;
use MailjetWp\GuzzleHttp\Exception\RequestException;
use MailjetWp\Mailjet\Client;
use MailjetWp\Mailjet\Resources;
use MailjetWp\GuzzleHttp\Exception\ConnectException;
use MailjetWp\Mailjet\Response;

/**
 * Define the internationalization functionality.
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      5.0.0
 * @package    Mailjet
 * @subpackage Mailjet/includes
 * @author     Your Name <email@example.com>
 */
class MailjetApi
{

    private static $mjApiClient = null;

    /**
     * Safely handle API exceptions with logging
     * @param Exception $e
     * @param string $methodName Name of the method for logging context
     */
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

    /**
     * @return Client
     * @throws Exception
     */
    public static function getApiClient(): Client
    {
        if (self::$mjApiClient instanceof Client) {
            return self::$mjApiClient;
        }

        $mailjetApikey = trim(Mailjet::getOption('mailjet_apikey'));
        $mailjetApiSecret = trim(Mailjet::getOption('mailjet_apisecret'));

        if (empty($mailjetApikey) || empty($mailjetApiSecret)) {
            MailjetLogger::error('Mailjet API initialization failed: credentials missing');
            throw new \Exception('Mailjet API credentials are missing. Please provide both API key and secret.');
        }

        // Validate API key format (should be alphanumeric, reasonable length)
        if (!\preg_match('/^[a-zA-Z0-9]{20,}$/', $mailjetApikey)) {
            MailjetLogger::error('Mailjet API key validation failed: invalid format');
            throw new \Exception('Mailjet API key format is invalid. Please verify your credentials.');
        }

        // Validate API secret format (should be alphanumeric, reasonable length)
        if (!\preg_match('/^[a-zA-Z0-9]{20,}$/', $mailjetApiSecret)) {
            MailjetLogger::error('Mailjet API secret validation failed: invalid format');
            throw new \Exception('Mailjet API secret format is invalid. Please verify your credentials.');
        }

        try {
            $mjClient = new Client($mailjetApikey, $mailjetApiSecret);
            $mjClient->addRequestOption(\CURLOPT_USERAGENT, 'wordpress-' . MAILJET_VERSION);
            $mjClient->addRequestOption('headers', ['User-Agent' => 'wordpress-' . MAILJET_VERSION]);
            
            // Configure timeout to prevent hanging (30 seconds default)
            $mjClient->addRequestOption(\CURLOPT_TIMEOUT, 30);
            $mjClient->addRequestOption(\CURLOPT_CONNECTTIMEOUT, 10);

            // Configure proxy if applicable
            self::configureProxy($mjClient);

            // Disable secure protocol if HTTPS is not supported
            if (!self::isHttpsSupported()) {
                $mjClient->setSecureProtocol(false);
                MailjetLogger::notice('Mailjet API: HTTPS not supported, using insecure protocol');
            }

            self::$mjApiClient = $mjClient;
            return self::$mjApiClient;
        } catch (Exception $e) {
            MailjetLogger::error('Mailjet API client initialization failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private static function configureProxy(Client $mjClient): void
    {
        // Validate all proxy constants are defined before attempting to configure
        $proxyRequired = [\defined('WP_PROXY_HOST'), \defined('WP_PROXY_PORT'), \defined('WP_PROXY_USERNAME'), \defined('WP_PROXY_PASSWORD')];
        
        if (\in_array(true, $proxyRequired, true) && !\in_array(false, $proxyRequired, true)) {
            // All four proxy constants are defined
            try {
                $proxyHost = WP_PROXY_HOST ?? '';
                $proxyPort = WP_PROXY_PORT ?? 0;
                $proxyUsername = WP_PROXY_USERNAME ?? '';
                $proxyPassword = WP_PROXY_PASSWORD ?? '';

                // Validate proxy values before applying
                if (!empty($proxyHost) && $proxyPort > 0 && $proxyPort < 65536) {
                    $mjClient->addRequestOption(\CURLOPT_HTTPPROXYTUNNEL, 1);
                    $mjClient->addRequestOption(\CURLOPT_PROXYAUTH, \CURLAUTH_BASIC);
                    $mjClient->addRequestOption(\CURLOPT_PROXY, $proxyHost . ':' . $proxyPort);
                    $mjClient->addRequestOption(\CURLOPT_PROXYPORT, $proxyPort);
                    $mjClient->addRequestOption(\CURLOPT_PROXYUSERPWD, $proxyUsername . ':' . $proxyPassword);
                    MailjetLogger::debug('Mailjet proxy configured: ' . $proxyHost . ':' . $proxyPort);
                } else {
                    MailjetLogger::warning('Mailjet proxy configuration invalid: host=' . $proxyHost . ', port=' . $proxyPort);
                }
            } catch (Exception $e) {
                MailjetLogger::error('Mailjet proxy configuration failed: ' . $e->getMessage());
            }
        }
    }

    private static function isHttpsSupported(): bool
    {
        // Use WordPress's native is_ssl() function for more reliable HTTPS detection
        // Falls back to manual check if wp function not available
        if (\function_exists('is_ssl')) {
            return is_ssl();
        }
        
        // Manual check: validate SERVER_PORT as integer (cast safety)
        $serverPort = isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : 0;
        $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        
        return $isHttps && $serverPort === 443;
    }

    /**
     * @return array|false
     */
    public static function getMailjetContactLists()
    {
        try {
            $mjApiClient = self::getApiClient();
        } catch (Exception $e) {
            MailjetLogger::error('Mailjet contact lists fetch failed: ' . $e->getMessage());
            return false;
        }
        $filters = array(
            'Limit' => '0',
            'Sort' => 'Name ASC',
        );
        try {
            $response = $mjApiClient->get(Resources::$Contactslist, array('filters' => $filters));
        } catch (ConnectException $e) {
            MailjetLogger::error('Mailjet API connection error (getMailjetContactLists): ' . $e->getMessage());
            return \false;
        } catch (RequestException $e) {
            MailjetLogger::error('Mailjet API request error (getMailjetContactLists): ' . $e->getMessage());
            return \false;
        }
        if ($response->success()) {
            return $response->getData();
        }

        MailjetLogger::warning('Mailjet API responded with failure status: ' . ($response->getStatus() ?? 'unknown'));
        return false;
    }

    /**
     * @param $listName
     * @return false|Response
     */
    public static function createMailjetContactList($listName)
    {
        if (empty($listName)) {
            return \false;
        }
        try {
            $mjApiClient = self::getApiClient();
        } catch (Exception $e) {
            return \false;
        }
        $body = array('Name' => $listName);
        try {
            $response = $mjApiClient->post(Resources::$Contactslist, array('body' => $body));
        } catch (ConnectException $e) {
            return \false;
        }
        return $response;
    }

    /**
     * @param $contactListId
     * @return array|false
     */
    public static function getContactListByID($contactListId)
    {
        if (empty($contactListId)) {
            return false;
        }
        try {
            $mjApiClient = self::getApiClient();
        } catch (Exception $e) {
            return false;
        }
        $filters = array('ID' => $contactListId);
        try {
            $response = $mjApiClient->get(Resources::$Contactslist, array('filters' => $filters));
        } catch (ConnectException $e) {
            return false;
        }
        if ($response->success()) {
            return $response->getData();
        }
        return false;
    }

    /**
     * @param $contactListId
     * @return array|false
     */
    public static function getSubscribersFromList($contactListId)
    {
        if (empty($contactListId)) {
            return \false;
        }
        try {
            $mjApiClient = self::getApiClient();
        } catch (Exception $e) {
            self::handleApiException($e, 'getSubscribersFromList');
            return \false;
        }
        $limit = 1000;
        $dataArray = array();
        $offset = 0;
        do {
            $filters = array(
                'ContactsList' => $contactListId,
                'Unsub' => \false,
                'Offset' => $offset,
                'Limit' => $limit,
                'Style' => 'Full',
            );
            try {
                $response = $mjApiClient->get(Resources::$Listrecipient, array('filters' => $filters));
            } catch (ConnectException $e) {
                MailjetLogger::error('Mailjet API connection error (getSubscribersFromList offset=' . $offset . '): ' . $e->getMessage());
                return \false;
            } catch (RequestException $e) {
                MailjetLogger::error('Mailjet API request error (getSubscribersFromList offset=' . $offset . '): ' . $e->getMessage());
                return \false;
            }
            if ($response->success()) {
                $dataArray[] = $response->getData();
            } else {
                MailjetLogger::error('Mailjet API error: failed to fetch subscribers (listId=' . $contactListId . ', offset=' . $offset . ')');
                return \false;
            }
            $offset += $limit;
        } while ($response->getCount() >= $limit);
        
        if (empty($dataArray)) {
            return array();
        }
        return \array_merge(...$dataArray);
    }

    /**
     * @return array|false
     */
    public static function getContactProperties()
    {
        try {
            $mjApiClient = self::getApiClient();
        } catch (Exception $e) {
            return \false;
        }
        $filters = array(
            'limit' => 0,
            'Sort' => 'Name ASC',
        );
        try {
            $response = $mjApiClient->get(Resources::$Contactmetadata, array('filters' => $filters));
        } catch (ConnectException $e) {
            return \false;
        }
        if ($response->success()) {
            return $response->getData();
        }

        return false;
    }

    /**
     * @param $name
     * @param string $type
     * @return array|false
     */
    public static function createMailjetContactProperty($name, string $type = 'str')
    {
        if (empty($name)) {
            return \false;
        }
        try {
            $mjApiClient = self::getApiClient();
        } catch (Exception $e) {
            return \false;
        }
        // Name: the name of the custom data field
        // DataType: the type of data that is being stored (this can be either a str, int, float or bool)
        // NameSpace: this can be either static or historic
        $body = array(
            'Datatype' => $type,
            'Name' => $name,
            'NameSpace' => 'static',
        );
        try {
            $response = $mjApiClient->post(Resources::$Contactmetadata, array('body' => $body));
        } catch (ConnectException $e) {
            return \false;
        }
        if ($response->success()) {
            return $response->getData();
        }

        return false;
    }

    /**
     * @return array|false
     */
    public static function getMailjetSegments()
    {
        try {
            $mjApiClient = self::getApiClient();
        } catch (Exception $e) {
            return \false;
        }
        try {
            $response = $mjApiClient->get(Resources::$Contactfilter);
        } catch (ConnectException $e) {
            return \false;
        }
        if ($response->success()) {
            return $response->getData();
        }

        return false;
    }

    /**
     * @param $name
     * @param $expression
     * @param string $description
     * @return array|false
     */
    public static function createMailjetSegment($name, $expression, string $description = '')
    {
        if (empty($name) || empty($expression)) {
            return \false;
        }
        try {
            $mjApiClient = self::getApiClient();
        } catch (Exception $e) {
            return \false;
        }
        $body = array(
            'Description' => $description,
            'Expression' => $expression,
            'Name' => $name,
        );
        try {
            $response = $mjApiClient->post(Resources::$Contactfilter, array('body' => $body));
        } catch (ConnectException $e) {
            return \false;
        }
        if ($response->success()) {
            return $response->getData();
        }

        return false;
    }

    /**
     * @return array|bool
     */
    public static function getMailjetSenders()
    {
        try {
            $mjApiClient = self::getApiClient();
        } catch (Exception $e) {
            return false;
        }
        $filters = array(
            'Limit' => '0',
            'Sort' => 'ID DESC',
        );
        try {
            $response = $mjApiClient->get(Resources::$Sender, array('filters' => $filters));
        } catch (ConnectException $e) {
            return false;
        }
        if ($response->success()) {
            return $response->getData();
        }

        return false;
    }

    /**
     * @return bool
     */
    public static function isValidAPICredentials(): bool
    {
        try {
            $mjApiClient = self::getApiClient();
            if ($mjApiClient === null) {
                MailjetLogger::warning('Mailjet API client is null during validation');
                return false;
            }
        } catch (Exception $e) {
            self::handleApiException($e, 'isValidAPICredentials');
            return false;
        }
        
        $filters = array('Limit' => '1');
        try {
            $response = $mjApiClient->get(Resources::$Contactmetadata, array('filters' => $filters));
            if ($response->success()) {
                MailjetLogger::notice('Mailjet API credentials validated successfully');
                return true;
            }
            MailjetLogger::warning('Mailjet API validation failed: ' . ($response->getStatus() ?? 'unknown status'));
            return false;
        } catch (ConnectException $e) {
            self::handleApiException($e, 'isValidAPICredentials');
            return false;
        } catch (RequestException $e) {
            self::handleApiException($e, 'isValidAPICredentials');
            return false;
        } catch (Exception $e) {
            self::handleApiException($e, 'isValidAPICredentials');
            return false;
        }
    }

    /**
     * Add or Remove a contact to a Mailjet contact list - It can process many or single contact at once
     *
     * @param $contactListId - int - ID of the contact list to sync contacts
     * @param $contacts - array('Email' => ContactEmail, 'Name' => ContactName, 'Properties' => array(propertyName1 => propertyValue1, ...));
     * @param string $action - 'addforce', 'adnoforce', 'remove'
     * @return array|bool
     */
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
        
        try {
            $mjApiClient = self::getApiClient();
        } catch (Exception $e) {
            self::handleApiException($e, 'syncMailjetContacts');
            return \false;
        }
        
        $body = array(
            'Action' => $action,
            'Contacts' => $contacts,
        );
        try {
            $response = $mjApiClient->post(
                Resources::$ContactslistManagemanycontacts,
                array(
                    'id' => $contactListId,
                    'body' => $body,
                )
            );
        } catch (ConnectException $e) {
            MailjetLogger::error('Mailjet API connection error (syncMailjetContacts): ' . $e->getMessage());
            return \false;
        } catch (RequestException $e) {
            MailjetLogger::error('Mailjet API request error (syncMailjetContacts): ' . $e->getMessage());
            return \false;
        }
        
        if ($response->success()) {
            return $response->getData();
        }

        MailjetLogger::warning('syncMailjetContacts failed: ' . ($response->getStatus() ?? 'unknown status'));
        return false;
    }

    /**
     * Add a contact to a Mailjet contact list
     */
    public static function syncMailjetContact($contactListId, $contact, $action = 'addforce')
    {
        // Validate inputs
        if (empty($contactListId) || !is_array($contact) || empty($contact['Email'])) {
            MailjetLogger::warning('syncMailjetContact: invalid input (listId=' . var_export($contactListId, true) . ', email=' . ($contact['Email'] ?? 'missing') . ')');
            return \false;
        }
        
        // Validate action parameter
        $validActions = ['addforce', 'addnoforce', 'remove', 'unsub'];
        if (!\in_array($action, $validActions)) {
            MailjetLogger::error('syncMailjetContact: invalid action parameter: ' . $action);
            return \false;
        }
        
        try {
            $mjApiClient = self::getApiClient();
        } catch (Exception $e) {
            self::handleApiException($e, 'syncMailjetContact');
            return \false;
        }
        
        $name = '';
        if (isset($contact['Properties'])) {
            $name = $contact['Properties']['firstname'] ?? '';
        }
        $body = array(
            'Name' => $name,
            'Action' => $action,
            'Email' => $contact['Email'],
            'Properties' => $contact['Properties'] ?? array(),
        );
        try {
            $response = $mjApiClient->post(
                Resources::$ContactslistManagecontact,
                array(
                    'id' => $contactListId,
                    'body' => $body,
                )
            );
        } catch (ConnectException $e) {
            MailjetLogger::error('Mailjet API connection error (syncMailjetContact): ' . $e->getMessage());
            return \false;
        } catch (RequestException $e) {
            MailjetLogger::error('Mailjet API request error (syncMailjetContact): ' . $e->getMessage());
            return \false;
        }
        
        if ($response->success()) {
            return $response->getData();
        }

        MailjetLogger::warning('syncMailjetContact failed: ' . ($response->getStatus() ?? 'unknown status'));
        return \false;
    }

    /**
     * Return TRUE if a contact already subscribed to the list and FALSE if it is not, or is added to the list but Unsubscribed
     *
     * @param $email
     * @param $listId
     * @param bool $getContactId
     * @return bool
     */
    public static function checkContactSubscribedToList($email, $listId, $getContactId = \false)
    {
        $exists = \false;
        $existsAndSubscribed = \false;
        try {
            $mjApiClient = self::getApiClient();
        } catch (Exception $e) {
            return \false;
        }
        $filters = array(
            'ContactEmail' => $email,
            'ContactsList' => $listId,
        );
        try {
            $response = $mjApiClient->get(Resources::$Listrecipient, array('filters' => $filters));
        } catch (ConnectException $e) {
            return \false;
        }
        if ($response->success() && $response->getCount() > 0) {
            $data = $response->getData();
            $exists = true;
            if (isset($data[0]['IsUnsubscribed']) && !$data[0]['IsUnsubscribed']) {
                $existsAndSubscribed = \true;
            }
            if ($getContactId && $exists && $existsAndSubscribed) {
                return $data[0]['ContactID'];
            }
        }
        return $exists && $existsAndSubscribed;
    }

    /**
     * @throws Exception
     */
    public static function isContactInList($email, $listId, $getContactId = \false)
    {
        $mjApiClient = self::getApiClient();
        $filters = array(
            'ContactEmail' => $email,
            'ContactsList' => $listId,
        );
        $response = $mjApiClient->get(Resources::$Listrecipient, array('filters' => $filters));
        if (!$response->success() || $response->getCount() <= 0) {
            return \false;
        }
        $data = $response->getData();
        if ($getContactId) {
            return $data[0]['ContactID'];
        }
        return \true;
    }

    /**
     * @throws Exception
     */
    public static function updateContactData($contactEmail, $data)
    {
        $mjApiClient = self::getApiClient();
        $body = array('Data' => $data ?? array());
        return $mjApiClient->put(array('contactdata', $contactEmail), array('body' => $body));
    }

    /**
     * @return false|string
     */
    public static function getProfileName()
    {
        try {
            $mjApiClient = self::getApiClient();
        } catch (Exception $e) {
            return \false;
        }
        try {
            $response = $mjApiClient->get(Resources::$Myprofile, array());
        } catch (ConnectException $e) {
            return \false;
        }
        $name = '';
        if ($response->success() && $response->getCount() > 0) {
            $data = $response->getData();
            if (isset($data[0]['Firstname']) && isset($data[0]['Lastname'])) {
                $name = $data[0]['Firstname'] . ' ' . $data[0]['Lastname'];
            }
        }
        return $name;
    }

    /**
     * @param $templateName
     * @return false|mixed
     */
    public static function getTemplateByName($templateName)
    {
        try {
            $mjApiClient = self::getApiClient();
        } catch (Exception $e) {
            return false;
        }
        try {
            $response = $mjApiClient->get(Resources::$Template, array('id' => 'apikey|' . $templateName));
        } catch (ConnectException $e) {
            return false;
        }
        if ($response->success() && $response->getCount() > 0) {
            return $response->getData()[0];
        }

        return false;
    }

    /**
     * @param $id
     * @return false|mixed
     */
    public static function getTemplateDetails($id)
    {
        try {
            $mjApiClient = self::getApiClient();
        } catch (Exception $e) {
            return \false;
        }
        try {
            $response = $mjApiClient->get(Resources::$TemplateDetailcontent, array('id' => $id));
        } catch (ConnectException $e) {
            return \false;
        }
        if ($response->success() && $response->getCount() > 0) {
            $data = $response->getData();
            return $data[0];
        }
        return \false;
    }

    /**
     * @param array $arguments
     * @return false|mixed
     */
    public static function createTemplate(array $arguments)
    {
        try {
            $mjApiClient = self::getApiClient();
        } catch (Exception $e) {
            return \false;
        }
        try {
            $response = $mjApiClient->post(Resources::$Template, $arguments);
        } catch (ConnectException $e) {
            return \false;
        }
        $data = $response->getData();
        if ($response->success() && $response->getCount() > 0) {
            return $data[0];
        }

        if ($data['ErrorCode'] === 'ps-0015') {
            return self::getTemplateByName($arguments['body']['Name']);
        }
        return \false;
    }

    /**
     * @param array $content
     * @return array|false
     */
    public static function createTemplateContent(array $content)
    {
        try {
            $mjApiClient = self::getApiClient();
        } catch (Exception $e) {
            return \false;
        }
        try {
            $response = $mjApiClient->post(Resources::$TemplateDetailcontent, $content);
        } catch (ConnectException $e) {
            return \false;
        }
        if ($response->success() && $response->getCount() > 0) {
            return $response->getData();
        }
        return \false;
    }

    /**
     * @param $content
     * @return array|false
     */
    public static function sendEmail($content)
    {
        try {
            $mjApiClient = self::getApiClient();
        } catch (Exception $e) {
            return \false;
        }
        $body = array('Messages' => array($content));

        try {
            $response = $mjApiClient->post(Resources::$Email, array('body' => $body), array('version' => 'v3.1'));
        } catch (ConnectException $e) {
            return \false;
        }
        if ($response->success()) {
            return $response->getData();
        }
        return \false;
    }
}
