<?php

namespace MailjetPlugin\Includes;

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
        if (self::$mjApiClient instanceof \Mailjet\Client) {
            return self::$mjApiClient;
        }
        $mailjetApikey = get_option('mailjet_apikey');
        $mailjetApiSecret = get_option('mailjet_apisecret');
        if (empty($mailjetApikey) || empty($mailjetApiSecret)) {
            throw new \Exception('Missing Mailjet API credentials');
        }
        self::$mjApiClient = new \Mailjet\Client($mailjetApikey, $mailjetApiSecret);
        return self::$mjApiClient;
    }

    public static function getMailjetContactLists()
    {
        $mjApiClient = self::getApiClient() ;

        $filters = [
            'Limit' => '0',
            'Sort' => 'Name ASC'
        ];
        $responseSenders = $mjApiClient->get(\Mailjet\Resources::$Contactslist, ['filters' => $filters]);
        if ($responseSenders->success()) {
            return $responseSenders->getData();
        } else {
            //return $responseSenders->getStatus();
            return false;
        }

    }


    public static function createMailjetContactList($listName)
    {
        if (empty($listName)) {
            return false;
        }

        $mjApiClient = self::getApiClient() ;

        $body = [
            'Name' => $listName
        ];
        $responseSenders = $mjApiClient->post(\Mailjet\Resources::$Contactslist, ['body' => $body]);
        if ($responseSenders->success()) {
            return $responseSenders->getData();
        } else {
            return false;
//            return $responseSenders->getStatus();
        }
    }



    public static function getMailjetSenders()
    {
        $mjApiClient = self::getApiClient() ;

        $filters = [
            'Limit' => '0',
            'Sort' => 'ID DESC'
        ];

        $responseSenders = $mjApiClient->get(\Mailjet\Resources::$Sender, ['filters' => $filters]);
        if ($responseSenders->success()) {
            return $responseSenders->getData();
        } else {
            //return $responseSenders->getStatus();
            return false;
        }

    }


    public static function isValidAPICredentials()
    {
        $mjApiClient = self::getApiClient() ;

        $filters = [
            'Limit' => '1'
        ];
        $responseSenders = $mjApiClient->get(\Mailjet\Resources::$Contactmetadata, ['filters' => $filters]);
        if ($responseSenders->success()) {
            return true;
            // return $responseSenders->getData();
        } else {
            return false;
            // return $responseSenders->getStatus();
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
        $mjApiClient = self::getApiClient() ;

        $body = [
            'Action' => $action,
            'Contacts' => $contacts
        ];

        $responseSenders = $mjApiClient->post(\Mailjet\Resources::$ContactslistManagemanycontacts, ['id' => $contactListId, 'body' => $body]);
        if ($responseSenders->success()) {
            return $responseSenders->getData();
        } else {
            return false;
//            return $responseSenders->getStatus();
        }
    }

}
