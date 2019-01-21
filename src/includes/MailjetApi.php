<?php

namespace MailjetPlugin\Includes;

use Exception;
use Mailjet\Client;
use Mailjet\Resources;

/**
 * Define the internationalization functionality.
 *
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

    public static function getApiClient()
    {
        if (self::$mjApiClient instanceof Client) {
            return self::$mjApiClient;
        }
        $mailjetApikey = get_option('mailjet_apikey');
        $mailjetApiSecret = get_option('mailjet_apisecret');
        if (empty($mailjetApikey) || empty($mailjetApiSecret)) {
            throw new \Exception('Missing Mailjet API credentials');
        }

        $mjClient = new Client($mailjetApikey, $mailjetApiSecret);
        $mjClient->addRequestOption(CURLOPT_USERAGENT, 'wordpress-' . MAILJET_VERSION);
        $mjClient->addRequestOption('headers', ['User-Agent' => 'wordpress-' . MAILJET_VERSION]);

        // Add proxy options for guzzle requests - if the Wordpress site is configured to use Proxy
        if (defined('WP_PROXY_HOST') && defined('WP_PROXY_PORT') && defined('WP_PROXY_USERNAME') && defined('WP_PROXY_PASSWORD')) {
            $mjClient->addRequestOption(CURLOPT_HTTPPROXYTUNNEL, 1);
            $mjClient->addRequestOption(CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
            $mjClient->addRequestOption(CURLOPT_PROXY, WP_PROXY_HOST . ':' . WP_PROXY_PORT);
            $mjClient->addRequestOption(CURLOPT_PROXYPORT, WP_PROXY_PORT);
            $mjClient->addRequestOption(CURLOPT_PROXYUSERPWD, WP_PROXY_USERNAME . ':' . WP_PROXY_PASSWORD);
        }

        // We turn of secure protocol for API requests if the wordpress does not support it
        if (empty($_SERVER['HTTPS']) || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'off') || $_SERVER['SERVER_PORT'] != 443) {
            $mjClient->setSecureProtocol(false);
        }

        self::$mjApiClient = $mjClient;
        return self::$mjApiClient;
    }

    public static function getMailjetContactLists()
    {
        $mjApiClient = self::getApiClient();

        $filters = [
            'Limit' => '0',
            'Sort' => 'Name ASC'
        ];
        $response = $mjApiClient->get(Resources::$Contactslist, ['filters' => $filters]);
        if ($response->success()) {
            return $response->getData();
        } else {
            //return $response->getStatus();
            return false;
        }
    }

    public static function createMailjetContactList($listName)
    {
        if (empty($listName)) {
            return false;
        }

        $mjApiClient = self::getApiClient();

        $body = [
            'Name' => $listName
        ];
        $response = $mjApiClient->post(Resources::$Contactslist, ['body' => $body]);
        return $response;
    }

    public static function isContactListActive($contactListId)
    {
        if (!$contactListId) {
            return false;
        }
        try {
            $mjApiClient = self::getApiClient();
        } catch (\Exception $e) {
            return false;
        }
        $filters = array(
            'ID' => $contactListId
        );
        $response = $mjApiClient->get(Resources::$Contactslist, array('filters' => $filters));
        if ($response->success()) {
            $data = $response->getData();
            if (isset($data[0]['IsDeleted'])) {
                // Return true if the list is not deleted
                return !$data[0]['IsDeleted'];
            }
        }
        return false;
    }

    public static function getContactProperties()
    {
        $mjApiClient = self::getApiClient();
        $filters = array(
            'limit' => 0,
            'Sort' => 'Name ASC'
        );
        $response = $mjApiClient->get(Resources::$Contactmetadata, array('filters' => $filters));
        if ($response->success()) {
            return $response->getData();
        } else {
            return false;
//            return $response->getStatus();
        }
    }

    public static function getPropertyIdByName($name)
    {
        if (!$name) {
           return false; 
        }
        $contactProperties = self::getContactProperties();
        if ($contactProperties) {
            foreach ($contactProperties as $property) {
                if ($property['Name'] === $name) {
                    return $property['ID'];
                }
            }
        }
        return false;
    }

    public static function createMailjetContactProperty($name, $type = "str")
    {
        if (empty($name)) {
            return false;
        }

        $mjApiClient = self::getApiClient();

//      Name: the name of the custom data field
//      DataType: the type of data that is being stored (this can be either a str, int, float or bool)
//      NameSpace: this can be either static or historic
        $body = [
            'Datatype' => $type,
            'Name' => $name,
            'NameSpace' => "static"
        ];
        $response = $mjApiClient->post(Resources::$Contactmetadata, ['body' => $body]);
        if ($response->success()) {
            return $response->getData();
        } else {
            return false;
//            return $response->getStatus();
        }
    }

    public static function getMailjetSenders()
    {
        $mjApiClient = self::getApiClient();

        $filters = [
            'Limit' => '0',
            'Sort' => 'ID DESC'
        ];

        $response = $mjApiClient->get(Resources::$Sender, ['filters' => $filters]);
        if ($response->success()) {
            return $response->getData();
        } else {
            //return $response->getStatus();
            return false;
        }
    }

    public static function isValidAPICredentials()
    {
        try {
            $mjApiClient = self::getApiClient();
        } catch (\Exception $e) {
            return false;
        }

        $filters = [
            'Limit' => '1'
        ];

        $response = $mjApiClient->get(Resources::$Contactmetadata, ['filters' => $filters]);
        if ($response->success()) {
            return true;
            // return $response->getData();
        } else {
            return false;
            // return $response->getStatus();
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
        $mjApiClient = self::getApiClient();

        $body = [
            'Action' => $action,
            'Contacts' => $contacts
        ];

        $response = $mjApiClient->post(Resources::$ContactslistManagemanycontacts, ['id' => $contactListId, 'body' => $body]);
        if ($response->success()) {
            return $response->getData();
        } else {
            return false;
//            return $response->getStatus();
        }
    }

    /**
     * Add a contact to a Mailjet contact list
     */
    public static function syncMailjetContact($contactListId, $contact, $action = 'addforce')
    {
        $mjApiClient = self::getApiClient();
        $name = isset($contact['Properties']['firstname']) ? $contact['Properties']['firstname'] : '';
        $body = [
            'Name' => $name,
            'Action' => $action,
            'Email' => $contact['Email'],
            'Properties' => $contact['Properties']
        ];
        $response = $mjApiClient->post(Resources::$ContactslistManagecontact, ['id' => $contactListId, 'body' => $body]);
        if ($response->success()) {
            return $response->getData();
        } else {
            return false;
        }
    }



    /**
     * Return TRUE if a contact already subscribed to the list and FALSE if it is not, or is added to the list but Unsubscribed
     *
     * @param $email
     * @param $listId
     * @return bool
     */
    public static function checkContactSubscribedToList($email, $listId)
    {
        $exists = false;
        $existsAndSubscribed = false;

        $mjApiClient = self::getApiClient();

        $filters = [
            'ContactEmail' => $email,
            'ContactsList' => $listId,
        ];

        $response = $mjApiClient->get(Resources::$Listrecipient, ['filters' => $filters]);

        if ($response->success() && $response->getCount() > 0) {
            $data = $response->getData();
            $exists = true;
            if (isset($data[0]['IsUnsubscribed']) && false == $data[0]['IsUnsubscribed']) {
                $existsAndSubscribed = true;
            }
        }

        return $exists && $existsAndSubscribed;
    }



}
