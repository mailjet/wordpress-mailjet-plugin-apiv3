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
 * @since      5.0.0
 * @package    Mailjet
 * @subpackage Mailjet/includes
 * @author     Your Name <email@example.com>
 */
class MailjetApi
{
    private static $mjApiClient = null;

    /**
     * @return Client|null
     * @throws Exception
     */
    public static function getApiClient()
    {
        if (self::$mjApiClient instanceof Client) {
            return self::$mjApiClient;
        }
        $mailjetApikey = get_option('mailjet_apikey');
        $mailjetApiSecret = get_option('mailjet_apisecret');
        if (empty($mailjetApikey) || empty($mailjetApiSecret)) {
            throw new Exception('Missing Mailjet API credentials');
        }
        $mjClient = new Client($mailjetApikey, $mailjetApiSecret);
        $mjClient->addRequestOption(\CURLOPT_USERAGENT, 'wordpress-' . MAILJET_VERSION);
        $mjClient->addRequestOption('headers', ['User-Agent' => 'wordpress-' . MAILJET_VERSION]);
        // Add proxy options for guzzle requests - if the Wordpress site is configured to use Proxy
        if (\defined('WP_PROXY_HOST') && \defined('WP_PROXY_PORT') && \defined('WP_PROXY_USERNAME') && \defined('WP_PROXY_PASSWORD')) {
            $mjClient->addRequestOption(\CURLOPT_HTTPPROXYTUNNEL, 1);
            $mjClient->addRequestOption(\CURLOPT_PROXYAUTH, \CURLAUTH_BASIC);
            $mjClient->addRequestOption(\CURLOPT_PROXY, WP_PROXY_HOST . ':' . WP_PROXY_PORT);
            $mjClient->addRequestOption(\CURLOPT_PROXYPORT, WP_PROXY_PORT);
            $mjClient->addRequestOption(\CURLOPT_PROXYUSERPWD, WP_PROXY_USERNAME . ':' . WP_PROXY_PASSWORD);
        }
        // We turn of secure protocol for API requests if the wordpress does not support it
        if (empty($_SERVER['HTTPS']) || !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'off' || $_SERVER['SERVER_PORT'] != 443) {
            $mjClient->setSecureProtocol(\false);
        }
        self::$mjApiClient = $mjClient;
        return self::$mjApiClient;
    }

    /**
     * @return array|false
     */
    public static function getMailjetContactLists()
    {
        try {
            $mjApiClient = self::getApiClient();
        } catch (Exception $e) {
            return false;
        }
        $filters = ['Limit' => '0', 'Sort' => 'Name ASC'];
        try {
            $response = $mjApiClient->get(Resources::$Contactslist, ['filters' => $filters]);
        } catch (ConnectException $e) {
            return \false;
        }
        if ($response->success()) {
            return $response->getData();
        }

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
        $body = ['Name' => $listName];
        try {
            $response = $mjApiClient->post(Resources::$Contactslist, ['body' => $body]);
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
        $filters = ['ID' => $contactListId];
        try {
            $response = $mjApiClient->get(Resources::$Contactslist, ['filters' => $filters]);
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
            return \false;
        }
        $limit = 1000;
        $dataArray = [];
        $offset = 0;
        do {
            $filters = ['ContactsList' => $contactListId, 'Unsub' => \false, 'Offset' => $offset, 'Limit' => $limit, 'Style' => 'Full'];
            try {
                $response = $mjApiClient->get(Resources::$Listrecipient, ['filters' => $filters]);
            } catch (ConnectException $e) {
                return \false;
            }
            if ($response->success()) {
                $dataArray[] = $response->getData();
            } else {
                return \false;
            }
            $offset += $limit;
        } while ($response->getCount() >= $limit);
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
        $filters = ['limit' => 0, 'Sort' => 'Name ASC'];
        try {
            $response = $mjApiClient->get(Resources::$Contactmetadata, ['filters' => $filters]);
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
    public static function createMailjetContactProperty($name, string $type = "str")
    {
        if (empty($name)) {
            return \false;
        }
        try {
            $mjApiClient = self::getApiClient();
        } catch (Exception $e) {
            return \false;
        }
        //      Name: the name of the custom data field
        //      DataType: the type of data that is being stored (this can be either a str, int, float or bool)
        //      NameSpace: this can be either static or historic
        $body = ['Datatype' => $type, 'Name' => $name, 'NameSpace' => "static"];
        try {
            $response = $mjApiClient->post(Resources::$Contactmetadata, ['body' => $body]);
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
        $body = ['Description' => $description, 'Expression' => $expression, 'Name' => $name];
        try {
            $response = $mjApiClient->post(Resources::$Contactfilter, ['body' => $body]);
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
        $filters = ['Limit' => '0', 'Sort' => 'ID DESC'];
        try {
            $response = $mjApiClient->get(Resources::$Sender, ['filters' => $filters]);
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
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
        $filters = ['Limit' => '1'];
        try {
            $response = $mjApiClient->get(Resources::$Contactmetadata, ['filters' => $filters]);
        } catch (ConnectException $e) {
            return false;
        } catch (RequestException $exception) {
            return false;
        }
        if ($response->success()) {
            return true;
        }

        return false;
    }

    /**
     * Add or Remove a contact to a Mailjet contact list - It can process many or single contact at once
     * @param $contactListId - int - ID of the contact list to sync contacts
     * @param $contacts - array('Email' => ContactEmail, 'Name' => ContactName, 'Properties' => array(propertyName1 => propertyValue1, ...));
     * @param string $action - 'addforce', 'adnoforce', 'remove'
     * @return array|bool
     */
    public static function syncMailjetContacts($contactListId, $contacts, $action = 'addforce')
    {
        try {
            $mjApiClient = self::getApiClient();
        } catch (Exception $e) {
            return \false;
        }
        $body = ['Action' => $action, 'Contacts' => $contacts];
        try {
            $response = $mjApiClient->post(Resources::$ContactslistManagemanycontacts, ['id' => $contactListId, 'body' => $body]);
        } catch (ConnectException $e) {
            return \false;
        }
        if ($response->success()) {
            return $response->getData();
        }

        return false;
    }

    /**
     * Add a contact to a Mailjet contact list
     */
    public static function syncMailjetContact($contactListId, $contact, $action = 'addforce')
    {
        try {
            $mjApiClient = self::getApiClient();
        } catch (Exception $e) {
            return \false;
        }
        $name = '';
        if (isset($contact['Properties'])) {
            $name = $contact['Properties']['firstname'] ?? '';
        }
        $body = ['Name' => $name, 'Action' => $action, 'Email' => $contact['Email'], 'Properties' => $contact['Properties'] ?? []];
        try {
            $response = $mjApiClient->post(Resources::$ContactslistManagecontact, ['id' => $contactListId, 'body' => $body]);
        } catch (ConnectException $e) {
            return \false;
        }
        if ($response->success()) {
            return $response->getData();
        }

        return \false;
    }

    /**
     * Return TRUE if a contact already subscribed to the list and FALSE if it is not, or is added to the list but Unsubscribed
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
        $filters = ['ContactEmail' => $email, 'ContactsList' => $listId];
        try {
            $response = $mjApiClient->get(Resources::$Listrecipient, ['filters' => $filters]);
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
        $filters = ['ContactEmail' => $email, 'ContactsList' => $listId];
        $response = $mjApiClient->get(Resources::$Listrecipient, ['filters' => $filters]);
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
        $body = ['Data' => $data ?? []];
        return $mjApiClient->put(['contactdata', $contactEmail], ['body' => $body]);
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
            $response = $mjApiClient->get(Resources::$Myprofile, []);
        } catch (ConnectException $e) {
            return \false;
        }
        $name = "";
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
            $response = $mjApiClient->get(Resources::$Template, ['id' => 'apikey|' . $templateName]);
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
            $response = $mjApiClient->get(Resources::$TemplateDetailcontent, ['id' => $id]);
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
        $body = ['Messages' => [$content]];

        try {
            $response = $mjApiClient->post(Resources::$Email, ['body' => $body], ['version' => 'v3.1']);
        } catch (ConnectException $e) {
            return \false;
        }
        if ($response->success()) {
            return $response->getData();
        }
        return \false;
    }
}
