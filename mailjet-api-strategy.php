<?php

/**
 * This is Api Strategy Interface
 * @author        Pavel Tashev
 * @author        Mailjet
 * @link        http://www.mailjet.com/
 */
# ============================================== Interface ============================================== #
interface WP_Mailjet_Api_Interface
{
    public function getSenders($params);

    public function getContactLists($params);

    public function getContactMetaProperties($params);

    public function createMetaContactProperty($params);

    public function addContact($params);

    public function updateContactData($params);

    public function removeContact($params);

    public function unsubContact($params);

    public function subContact($params);

    public function getAuthToken($params);

    public function validateEmail($email);
}


# ============================================== Strategy ============================================== #
# Strategy ApiV1
class WP_Mailjet_Api_Strategy_V1 extends WP_Mailjet_Api_V1 implements WP_Mailjet_Api_Interface
{
    /**
     * Get full list of senders
     *
     * @param (array) $param = array('limit', ...)
     * @return (object)
     */
    public function getSenders($params)
    {
        // Set input parameters
        $input = array();
        if (isset($params['limit'])) $input['limit'] = $params['limit'];

        // Get the list
        $userSenderList = $this->userSenderList();
        if($userSenderList) {
            $response = $userSenderList->senders;
        }

        // Check if the list exists
        if (isset($response)) {
            $senders = array();
            $senders['domain'] = array();
            $senders['email'] = array();

            foreach ($response as $sender) {
                if ($sender->status == 'active') {
                    if (substr($sender->email, 0, 2) == '*@')
                        $senders['domain'][] = substr($sender->email, 2, strlen($sender->email)); // This is domain
                    else
                        $senders['email'][] = $sender->email; // This is email
                }
            }
            return $senders;
        }

        return (object)array('Status' => 'ERROR');
    }

    /**
     * Get full list of contact lists
     *
     * @param (array) $param = array('limit', ...)
     * @return (object)
     */
    public function getContactLists($params)
    {
        // Set input parameters
        $input = array();
        if (isset($params['limit'])) $input['limit'] = $params['limit'];

        // Get the list
        $response = $this->listsAll($input);

        // Check if the list exists
        if (isset($response->status) && $response->status == 'OK') {
            $lists = array();
            foreach ($response->lists as $list) {
                $lists[] = array(
                    'value' => $list->id,
                    'label' => $list->label,
                );
            }
            return $lists;
        }

        return (object)array('Status' => 'ERROR');
    }

    /**
     * Get list of user contact meta properties. v1 users do not have such feature
     *
     * @param array
     * @return null
     */
    public function getContactMetaProperties($params)
    {
        return array();
    }

    public function createMetaContactProperty($params)
    {
        return array();
    }

    public function findRecipient($params){
        $params['method'] = 'GET';
        $params['id'] = $params['ContactsList'];
        unset($params['ContactsList']);
        $email = $params['ContactEmail'];
        unset($params['ContactEmail']);
        $response = $this->listsContacts($params);
        if (empty($response->result)) {
            return false;
        }
        $contactExists = false;
        foreach($response->result as $contact) {
            if ($contact->email === $email) {
                $contactExists = true;
                break;
            }
        }
        return $contactExists;
    }

    /**
     * Add a contact to a contact list with ID = ListID
     *
     * @param (array) $param = array('Email', 'ListID', ...)
     * @return (object)
     */
    public function addContact($params)
    {
        // Check if the input data is OK
        if (!is_numeric($params['ListID']) || !$this->validateEmail($params['Email']))
            return (object)array('Status' => 'ERROR');

        // Add the contact
        $response = $this->listsAddContact(array(
            'method' => 'POST',
            'contact' => $params['Email'],
            'id' => $params['ListID']
        ));

        // Check if the contact is added
        if ($response)
            return (object)array('Status' => 'OK');

        return (object)array('Status' => 'ERROR');
    }

    public function updateContactData($params)
    {
    }

    /**
     * Remove a contact from a contact list with ID = ListID
     *
     * @param (array) $param = array('Email', 'ListID', ...)
     * @return (object)
     */
    public function removeContact($params)
    {
        // Check if the input data is OK
        if (!is_numeric($params['ListID']) || !$this->validateEmail($params['Email']))
            return (object)array('Status' => 'ERROR');

        // Unsubscribe the contact
        $response = $this->listsRemoveContact(array(
            'method' => 'POST',
            'contact' => $params['Email'],
            'id' => $params['ListID']
        ));

        // Check if the contact is added
        if ($response)
            return (object)array('Status' => 'OK');

        return (object)array('Status' => 'OK');
    }

    /**
     * Unsubscribe a contact from a contact list with ID = ListID
     *
     * @param (array) $param = array('Email', 'ListID', ...)
     * @return (object)
     */
    public function unsubContact($params)
    {
        // Check if the input data is OK
        if (!is_numeric($params['ListID']) || !$this->validateEmail($params['Email']))
            return (object)array('Status' => 'ERROR');

        // Unsubscribe the contact
        $response = $this->listsUnsubContact(array(
            'method' => 'POST',
            'contact' => $params['Email'],
            'id' => $params['ListID']
        ));

        // Check if the contact is added
        if ($response)
            return (object)array('Status' => 'OK');

        return (object)array('Status' => 'OK');
    }

    /**
     * Subscribe a contact to a contact list with ID = ListID
     *
     * @param (array) $param = array('Email', 'ListID', ...)
     * @return (object)
     */
    public function subContact($params)
    {
        // Check if the input data is OK
        if (!is_numeric($params['ListID']) || !$this->validateEmail($params['Email']))
            return (object)array('Status' => 'ERROR');

        // Subscribe the user
        $response = $this->listsAddContact(array(
            'method' => 'POST',
            'id' => $params['ListID'],
            'contact' => $params['Email'],
            'force' => 1,
        ));

        // Check if the contact is added
        if ($response) {
            return (object)array(
                'Status' => 'OK',
                'Response' => $response
            );
        }


        return (object)array(
            'Status' => 'ERROR'
        );
    }

    /**
     * Get the authentication token for the iframes
     *
     * @param (array) $param = array('APIKey', 'SecretKey', 'MailjetToken', ...)
     * @return (object)
     */
    public function getAuthToken($params)
    {
        // Check if the input data is OK
        if (strlen(trim($params['APIKey'])) == 0 || strlen(trim($params['SecretKey'])) == 0 || strlen(trim($params['MailjetToken'])) == 0)
            return (object)array('Status' => 'ERROR');

        if ($op = $params['MailjetToken']) {
            $op = json_decode($op);
            if ($op->timestamp > time() - 3600)
                return $op->token;
        }

        if (!defined('WPLANG'))
            $locale = 'en';
        else {
            $locale = substr(WPLANG, 0, 2);
            if (!in_array($locale, array('en', 'fr', 'es', 'de')))
                $locale = 'en';
        }

        $res = wp_remote_post(
            $this->apiUrl . '/apiKeyauthenticate?output=json',
            array(
                'headers' => array('Authorization' => 'Basic ' . base64_encode($params['APIKey'] . ':' . $params['SecretKey'])),
                'body' => array(
                    'allowed_access[0]' => 'stats',
                    'allowed_access[1]' => 'contacts',
                    'allowed_access[2]' => 'campaigns',
                    'lang' => $locale,
                    'default_page' => 'campaigns',
                    'type' => 'page',
                    'apikey' => $params['APIKey']
                )
            )
        );

        if (is_array($res)) {
            $resp = json_decode($res['body']);
            if ($resp->status == 'OK') {
                update_option('mailjet_token' . $_SERVER['REMOTE_ADDR'], json_encode(array('token' => $resp->token, 'timestamp' => time())));
                return $resp->token;
            }
        }

        return (object)array('Status' => 'ERROR');
    }

    /**
     * Validate if $email is real email
     *
     * @param (string) $email
     * @return (boolean) TRUE|FALSE
     */
    public function validateEmail($email)
    {
        return (preg_match("/(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)/", $email) || !preg_match("/^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/", $email)) ? FALSE : TRUE;
    }
}


# Strategy ApiV3
class WP_Mailjet_Api_Strategy_V3 extends WP_Mailjet_Api_V3 implements WP_Mailjet_Api_Interface
{

    public function getMetaDataContact()
    {
        return $this->{'metadata/contact'}();
    }

    /**
     * Get full list of senders
     *
     * @param (array) $param = array('limit', ...)
     * @return (object)
     */
    public function getSenders($params)
    {
        // Set input parameters
        $input = array();
        if (isset($params['limit'])) {
            $input['limit'] = $params['limit'];
        }

        // Get the list
        $response = $this->sender($input);

        // Check if the list exists
        if (isset($response->Data)) {
            $senders = array();
            $senders['domain'] = array();
            $senders['email'] = array();

            foreach ($response->Data as $sender) {
                if ($sender->Status == 'Active') {
                    if (substr($sender->Email, 0, 2) == '*@')
                        $senders['domain'][] = substr($sender->Email, 2, strlen($sender->Email)); // This is domain
                    else
                        $senders['email'][] = $sender->Email; // This is email
                }
            }
            return $senders;
        }

        return (object)array('Status' => 'ERROR');
    }

    /**
     * Get full list of contact lists
     *
     * @param (array) $param = array('limit', ...)
     * @return (object)
     */
    public function getContactLists($params)
    {
        // Set input parameters
        $input = array(//'akid'	=> $this->_akid
        );
        if (isset($params['limit'])) $input['limit'] = $params['limit'];

        // Get the list
        $response = $this->liststatistics($input);

        // Check if the list exists
        if (isset($response->Data)) {
            $lists = array();
            foreach ($response->Data as $list) {
                $lists[] = array(
                    'value' => $list->ID,
                    'label' => $list->Name,
                );
            }
            return $lists;
        }

        return (object)array('Status' => 'ERROR');
    }

    /**
     * Get list of user contact meta properties. v3 users only
     *
     * @param array
     * @return object
     */
    public function getContactMetaProperties($params)
    {
        return $this->contactmetadata($params);
    }

    /**
     * Create a new meta contact property
     *
     * @param array
     * @return object
     */
    public function createMetaContactProperty($params)
    {
        $response = $this->contactmetadata(array(
            'method' => 'POST',
            'Datatype' => $params['dataType'],
            'Name' => $params['name'],
            'NameSpace' => 'static',
        ));
        if ((empty($response->ErrorMessage) && !empty($response))) {
            $status = 'OK';
            $msg = 'Property created.  Please drag your new contact property to the Selected Properties section above.';
        } elseif ((!empty($response->ErrorMessage) && strpos($response->ErrorMessage, 'already exists'))) {
            $status = 'Error';
            $msg = 'Property already exists';
        } else {
            $status = 'Error';
            $msg = 'Property could not be created';
        }
        return array(
            'status' => $status,
            'message' => $msg
        );
    }

    public function updateContactData($params)
    {
        $response = $this->contactdata($params);
        if (empty($response->Data) || empty($response->Count)) {
            return $response;
        }
        return $response;
    }

    /**
     * Add a contact to a contact list with ID = ListID
     *
     * @param (array) $param = array('Email', 'ListID', ...)
     * @return (object)
     */
    public function addContact($params)
    {

        // Check if the input data is OK
        if (!is_numeric($params['ListID']) || !$this->validateEmail($params['Email']))
            return (object)array('Status' => 'ERROR');

        // Add the contact
        $result = $this->manycontacts(array(
            'method' => 'POST',
            'Action' => 'Add',
            'Addresses' => array($params['Email']),
            'ListID' => $params['ListID'],
        ));

        // Check if any error
        if (isset($result->Data['0']->Errors->Items)) {
            if (strpos($result->Data['0']->Errors->Items[0]->ErrorMessage, 'duplicate') !== FALSE)
                return (object)array('Status' => 'DUPLICATE');
            else
                return (object)array('Status' => 'ERROR');
        }

        $this->subContact($params);
        return (object)array(
            'Status' => 'OK',
            'Response' => $result
        );
    }

    public function findRecipient($params)
    {
        $params['method'] = 'GET';
        return $this->listrecipient($params);
    }

    /**
     * Remove a contact from a contact list with ID = ListID
     *
     * @param (array) $param = array('Email', 'ListID', ...)
     * @return (object)
     */
    public function removeContact($params)
    {
        // Check if the input data is OK
        if (!is_numeric($params['ListID']) || !$this->validateEmail($params['Email']))
            return (object)array('Status' => 'ERROR');

        // Get the contact
        $result = $this->listrecipient(array(
            //'akid'          => $this->_akid,
            'method' => 'GET',
            'ContactsList' => $params['ListID'],
            'ContactEmail' => $params['Email']
        ));
        if ($result->Count > 0) {
            foreach ($result->Data as $contact) {
                // Remove the contact
                $response = $this->listrecipient(array(
                    //'akid'				=> $this->_akid,
                    'method' => 'delete',
                    'ID' => $contact->ID
                ));
            }

            // Check if the unsubscribe is done correctly
            if (isset($response->Data[0]->ID))
                return (object)array('Status' => 'OK');
        }

        return (object)array('Status' => 'ERROR');
    }

    /**
     * Unsubscribe a contact from a contact list with ID = ListID
     *
     * @param (array) $param = array('Email', 'ListID', ...)
     * @return (object)
     */
    public function unsubContact($params)
    {
        // Check if the input data is OK
        if (!is_numeric($params['ListID']) || !$this->validateEmail($params['Email']))
            return (object)array('Status' => 'ERROR');

        // Get the contact
        $result = $this->listrecipient(array(
            'method' => 'GET',
            'ListID' => $params['ListID'],
            'ContactEmail' => $params['Email']
        ));
        if ($result->Count > 0) {
            foreach ($result->Data as $contact) {
                if ($contact->IsUnsubscribed !== TRUE) {
                    $response = $this->listrecipient(array(
                        //'akid'    			=> $this->_akid,
                        'method' => 'PUT',
                        'ID' => $contact->ID,
                        'IsUnsubscribed' => 'true',
                        'UnsubscribedAt' => date("Y-m-d\TH:i:s\Z", time()),
                    ));
                }
            }

            // Check if the unsubscribe is done correctly
            if (isset($response->Data[0]->ID))
                return (object)array('Status' => 'OK');
        }

        return (object)array('Status' => 'ERROR');
    }

    /**
     * Subscribe a contact to a contact list with ID = ListID
     *
     * @param (array) $param = array('Email', 'ListID', ...)
     * @return (object)
     */
    public function subContact($params)
    {
        // Check if the input data is OK
        if (!is_numeric($params['ListID']) || !$this->validateEmail($params['Email']))
            return (object)array('Status' => 'ERROR');

        // Get the contact
        $result = $this->listrecipient(array(
            'method' => 'GET',
            'ListID' => $params['ListID'],
            'ContactEmail' => $params['Email']
        ));

        if ($result->Count > 0) {
            foreach ($result->Data as $contact) {
                if ($contact->IsUnsubscribed === TRUE) {
                    $response = $this->listrecipient(array(
                        //'akid'    			=> $this->_akid,
                        'method' => 'PUT',
                        'ID' => $contact->ID,
                        'IsUnsubscribed' => 'false',
                    ));
                }
            }

            // Check if the subscribe is done correctly
            if (isset($response->Data[0]->ID))
                return (object)array('Status' => 'OK');
        }

        return (object)array('Status' => 'ERROR');
    }

    /**
     * Get the authentication token for the iframes
     *
     * @param (array) $param = array('APIKey', 'SecretKey', 'MailjetToken', ...)
     * @return (object)
     */
    public function getAuthToken($params)
    {
        // Check if the input data is OK
        if (strlen(trim($params['APIKey'])) == 0 || strlen(trim($params['SecretKey'])) == 0 || strlen(trim($params['MailjetToken'])) == 0)
            return (object)array('Status' => 'ERROR');

        // Get the ID of the Api Key
        $api_key_response = $this->apikey(array(
            'method' => 'GET',
            'APIKey' => $params['APIKey']
        ));

        // Check if the response contains data
        if (!isset($api_key_response->Data[0]->ID))
            return (object)array('Status' => 'ERROR');

        // Get token
        $response = $this->apitoken(array(
            'AllowedAccess' => 'campaigns,contacts,reports,stats,preferences,pricing,account',
            'method' => 'POST',
            'APIKeyID' => $api_key_response->Data[0]->ID,
            'TokenType' => 'url',
            'CatchedIp' => $_SERVER['REMOTE_ADDR'],
            'log_once' => TRUE,
            'IsActive' => TRUE,
            'SentData' => serialize(array('plugin' => 'wordpress-3.0')),
        ));

        // Get and return the token
        if (isset($response->Data) && count($response->Data) > 0)
            return $response->Data[0]->Token;

        return (object)array('Status' => 'ERROR');
    }

    /**
     * Validate if $email is real email
     *
     * @param (string) $email
     * @return (boolean) TRUE|FALSE
     */
    public function validateEmail($email)
    {
        return (preg_match("/(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)/", $email) || !preg_match("/^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/", $email)) ? FALSE : TRUE;
    }
}


# ============================================== Context ============================================== #
class WP_Mailjet_Api
{
    private $context;
    public $version;
    public $mj_host;
    public $mj_mailer;

    public function __construct($mailjet_username, $mailjet_password)
    {
        // Is user API version recorded in DB?
        $userApiVersion = get_option('mailjet_user_api_version');
        if ($userApiVersion === false) {
            $userApiVersion = $this->findUserApiVersion($mailjet_username, $mailjet_password);
            update_option('mailjet_user_api_version', $userApiVersion);
        }
        $userApiVersion = (int)$userApiVersion;

        switch ($userApiVersion) {
            case 1:
                $this->setContext(new WP_Mailjet_Api_Strategy_V1($mailjet_username, $mailjet_password));
                $this->mj_host = 'in.mailjet.com';
                break;
            case 3:
                $this->setContext(new WP_Mailjet_Api_Strategy_V3($mailjet_username, $mailjet_password));
                $this->mj_host = 'in-v3.mailjet.com';
                break;
            default:
                $this->clearContext();
        }

        if (false !== $this->context) {
            $this->mj_mailer = 'X-Mailer:WP-Mailjet/0.1';
            $this->version = $this->context->getVersion();
        }
    }

    /**
     * @param $mailjet_username
     * @param $mailjet_password
     * @return bool|int
     */
    public function findUserApiVersion($mailjet_username, $mailjet_password)
    {
        if ($this->isV3User($mailjet_username, $mailjet_password)) {
            return 3;
        } elseif ($this->isV1User($mailjet_username, $mailjet_password)) {
            return 1;
        }
        return false;
    }

    public function isV1User($mailjet_username, $mailjet_password)
    {
        $this->setContext(new WP_Mailjet_Api_Strategy_V1($mailjet_username, $mailjet_password));
        $response = $this->context->getSenders(array('limit' => 1));
        if (isset($response->Status) && $response->Status == 'ERROR') {
            return false;
        }
        return true;
    }

    public function isV3User($mailjet_username, $mailjet_password)
    {
        $v3api = new WP_Mailjet_Api_Strategy_V3($mailjet_username, $mailjet_password);
        $response = $v3api->getMetaDataContact();
        return !empty($response->Count);
    }

    /**
     * Set the context of the Api - V1 or V3
     *
     * @param WP_Mailjet_Api_Interface $context
     * @return void
     */
    private function setContext(WP_Mailjet_Api_Interface $context)
    {
        $this->context = $context;
    }

    /**
     * Clear the context
     *
     * @param void
     * @return void
     */
    private function clearContext()
    {
        $this->context = FALSE;
    }

    public function findRecipient($params)
    {
        return $this->context->findRecipient($params);
    }

    /**
     * Get full list of senders
     *
     * @param (array) $param = array('limit', ...)
     * @return (object)
     */
    public function getSenders($params)
    {
        // Check if we have context, if no, return error
        if ($this->context === FALSE)
            return (object)array('Status' => 'ERROR');

        return $this->context->getSenders($params);
    }


    public function updateContactData($params)
    {
        // Check if we have context, if no, return error
        if ($this->context === FALSE)
            return (object)array('Status' => 'ERROR');
        return $this->context->updateContactData($params);
    }

    /**
     * Get full list of contact lists
     *
     * @param (array) $param = array('limit', ...)
     * @return (object)
     */
    public function getContactLists($params)
    {
        // Check if we have context, if no, return error
        if ($this->context === FALSE)
            return (object)array('Status' => 'ERROR');

        return $this->context->getContactLists($params);
    }

    /**
     * Get list of user contact meta properties
     *
     * @param array
     * @return object
     */
    public function getContactMetaProperties($params)
    {
        if ($this->context === FALSE) {
            return (object)array('Status' => 'ERROR');
        }
        return $this->context->getContactMetaProperties($params);
    }

    /**
     * Create a new meta contact property
     *
     * @param array
     * @return object
     */
    public function createMetaContactProperty($params)
    {
        if ($this->context === FALSE) {
            return (object)array('Status' => 'ERROR');
        }
        return $this->context->createMetaContactProperty($params);
    }

    /**
     * Add a contact to a contact list with ID = ListID
     *
     * @param (array) $param = array('Email', 'ListID', ...)
     * @return (object)
     */
    public function addContact($params)
    {
        // Check if we have context, if no, return error
        if ($this->context === FALSE)
            return (object)array('Status' => 'ERROR');

        return $this->context->addContact($params);
    }

    /**
     * Remove a contact from a contact list with ID = ListID
     *
     * @param (array) $param = array('Email', 'ListID', ...)
     * @return (object)
     */
    public function removeContact($params)
    {
        // Check if we have context, if no, return error
        if ($this->context === FALSE)
            return (object)array('Status' => 'ERROR');

        return $this->context->removeContact($params);
    }

    /**
     * Unsubscribe a contact from a contact list with ID = ListID
     *
     * @param (array) $param = array('Email', 'ListID', ...)
     * @return (object)
     */
    public function unsubContact($params)
    {
        // Check if we have context, if no, return error
        if ($this->context === FALSE)
            return (object)array('Status' => 'ERROR');

        return $this->context->unsubContact($params);
    }

    /**
     * Subscribe a contact to a contact list with ID = ListID
     *
     * @param (array) $param = array('Email', 'ListID', ...)
     * @return (object)
     */
    public function subContact($params)
    {
        // Check if we have context, if no, return error
        if ($this->context === FALSE)
            return (object)array('Status' => 'ERROR');

        return $this->context->subContact($params);
    }

    /**
     * Get the authentication token for the iframes
     *
     * @param (array) $param = array('APIKey', 'SecretKey', 'MailjetToken', ...)
     * @return (object)
     */
    public function getAuthToken($params)
    {
        // Check if we have context, if no, return error
        if ($this->context === FALSE)
            return (object)array('Status' => 'ERROR');

        return $this->context->getAuthToken($params);
    }

    /**
     * Validate if $email is real email
     *
     * @param (string) $email
     * @return (boolean) TRUE|FALSE
     */
    public function validateEmail($email)
    {
        // Check if we have context, if no, return error
        if ($this->context === FALSE)
            return (object)array('Status' => 'ERROR');

        return $this->context->validateEmail($email);
    }
}