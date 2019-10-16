<?php

namespace MailjetIframe;

use MailjetIframe\MailjetApi;
use MailjetIframe\MailjetException;


/**
 *
 * @package MailjetIframes API v.3
 */
class MailjetIframe
{

    const ON = 'on';
    const OFF = 'off';

    const PAGE_STATS = 'stats';
    const PAGE_CAMPAIGNS = 'campaigns';
    const PAGE_CONTACTS = 'contacts';
    const PAGE_AUTOMATION = 'workflow';
    const PAGE_WIDGET = 'widget';
    const PAGE_TEMPLATES = 'templates';
    const PAGE_EDIT_TEMPLATE = 'template/{param}/build';

    const SESSION_NAME = 'MailjetIframeToken';
    const SESSION_SET = 'MailjetIframeTokenSet';

    /**
     *
     * @var MailjetApi
     */
    private $mailjetApi;

    /**
     *
     * @var array
     */
    private $locales = array('fr_FR', 'en_US', 'en_GB', 'en_EU', 'de_DE', 'es_ES', 'it_IT');

    /**
     *
     * @var array
     */
    private $allowedPages = array(
        self::PAGE_STATS,
        self::PAGE_CONTACTS,
        self::PAGE_CAMPAIGNS,
        self::PAGE_AUTOMATION,
        self::PAGE_WIDGET,
        self::PAGE_TEMPLATES,
        self::PAGE_EDIT_TEMPLATE,
    );

    /**
     *
     * @var string
     */
    private $url = 'https://app.mailjet.com/';

    /**
     *
     * @var string
     */
    private $callback = '';

    /**
     *
     * @var string
     */
    private $locale = 'fr_FR';

    /**
     *
     * @var integer
     */
    private $sessionExpiration = 3600;

    /**
     *
     * @var array
     */
    private $tokenAccessAvailable = array(
        'campaigns',
        'contacts',
        'stats',
        'email_automation',
        'widget',
        'transactional',
    );

    /**
     *
     * @var string
     */
    private $tokenAccess = '';

    /**
     *
     * @var string
     */
    private $documentationProperties = self::ON;

    /**
     *
     * @var string
     */
    private $newContactListCreation = self::ON;

    /**
     *
     * @var string
     */
    private $menu = self::ON;

    /**
     * Flag to mark if to display the black campaign name title bar in the iframe
     *
     * @access  private
     * @var string 'on'/'off'
     */
    private $showBar = self::ON;

    /**
     * Flag to mark if "create campaign" button in the campaign page should be displayed
     *
     * @access  private
     * @var string 'on'/'off'
     */
    private $crc = self::OFF;

    /**
     * Flag to mark if MJ sending policy should be removed or not
     *
     * @access  private
     * @var string 'on'/'off'
     */
    private $sp = self::OFF;

    /**
     *
     * @var string
     */
    private $initialPage = self::PAGE_STATS;

    /**
     * Flag to mark if footer should be displayed
     *
     * @access  private
     * @var     boolean $_showFooter
     */
    private $showFooter = self::ON;

    /**
     *
     * @param string $apitKey
     * @param string $secretKey
     * @param string $secretKey
     */
    public function __construct($apitKey, $secretKey, $startSession = true)
    {
        if (true === $startSession) {
            $this->startSession();
        }
        $this->mailjetApi = new MailjetApi($apitKey, $secretKey);
    }

    /**
     * Turn email footer signature on/off
     * @param string $flag
     */
    public function turnFooter($flag)
    {
        $this->showFooter = $flag;

        return $this;
    }

    /**
     *
     * @param integer $seconds
     * @return \MailjetIframe
     */
    public function setTokenExpiration($seconds = 600)
    {
        if (!is_numeric($seconds)) {
            throw new MailjetException(
                "Token expiration should be a valid number."
            );
        }

        if ($seconds <= 0) {
            throw new MailjetException(
                "Token expiration should be greater than 0"
            );
        }

        $this->sessionExpiration = $seconds;
        return $this;
    }

    /**
     *
     * @param string $callback
     * @param boolean $isEncoded
     * @return \MailjetIframe
     */
    public function setCallback($callback = '', $isEncoded = false)
    {
        if ($isEncoded) {
            $this->callback = $callback;
        } else {
            $this->callback = urldecode($callback);
        }

        return $this;
    }

    /**
     *
     * @param string $locale
     * @return \MailjetIframe
     * @throws MailjetException
     */
    public function setLocale($locale = 'fr_FR')
    {
        if (!in_array($locale, $this->locales)) {
            throw new MailjetException(
                "{$locale} is not supported."
            );
        }

        $this->locale = $locale;
        return $this;
    }

    /**
     *
     * @param array $access
     * @return \MailjetIframe
     */
    public function setTokenAccess(array $access = array())
    {
        foreach ($access as $value) {
            if (!in_array($value, $this->tokenAccessAvailable)) {
                throw new MailjetException(
                    "{$value} is not a valid token access."
                );
            }
        }

        $this->tokenAccess = implode(', ', $access);
        return $this;
    }

    /**
     *
     * @param string $flag
     * @return \MailjetIframe
     * @throws MailjetException
     */
    public function turnDocumentationProperties($flag = self::ON)
    {
        if (!$this->isAllowedOnOffParameter($flag)) {
            throw new MailjetException(
                "Documentation properties requires a valid on/off parameter."
            );
        }

        $this->documentationProperties = $flag;
        return $this;
    }

    /**
     *
     * @param string $flag
     * @return \MailjetIframe
     * @throws MailjetException
     */
    public function turnNewContactListCreation($flag = self::ON)
    {
        if (!$this->isAllowedOnOffParameter($flag)) {
            throw new MailjetException(
                "New contact list creation requires a valid on/off parameter."
            );
        }

        $this->newContactListCreation = $flag;
        return $this;
    }

    /**
     *
     * @param string $flag
     * @return \MailjetIframe
     * @throws MailjetException
     */
    public function turnMenu($flag = self::ON)
    {
        if (!$this->isAllowedOnOffParameter($flag)) {
            throw new MailjetException(
                "Menu requires a valid on/off parameter."
            );
        }

        $this->menu = $flag;
        return $this;
    }

    /**
     *
     * @param string $flag
     * @return \MailjetIframe
     * @throws MailjetException
     */
    public function turnBar($flag = self::ON)
    {
        if (!$this->isAllowedOnOffParameter($flag)) {
            throw new MailjetException(
                "Bar requires a valid on/off parameter."
            );
        }

        $this->showBar = $flag;
        return $this;
    }


    /**
     *
     * @param string $flag
     * @return \MailjetIframe
     * @throws MailjetException
     */
    public function turnCreateCampaignButton($flag = self::ON)
    {
        if (!$this->isAllowedOnOffParameter($flag)) {
            throw new MailjetException(
                "Create campaign requires a valid on/off parameter."
            );
        }

        $this->crc = $flag;
        return $this;
    }


    /**
     *
     * @param string $flag
     * @return \MailjetIframe
     * @throws MailjetException
     */
    public function turnSendingPolicy($flag = self::ON)
    {
        if (!$this->isAllowedOnOffParameter($flag)) {
            throw new MailjetException(
                "Sending policy requires a valid on/off parameter."
            );
        }

        $this->sp = $flag;
        return $this;
    }


    /**
     *
     * @param string $page
     * @param string $param
     * @return \MailjetIframe
     * @throws MailjetException
     */
    public function setInitialPage($page = self::PAGE_STATS, $param = null)
    {
        if (!in_array($page, $this->allowedPages)) {
            throw new MailjetException(
                "{$page} is unknown."
            );
        }

        if (strpos($page, '{param}') !== false) {
            if (!isset($param)) {
                throw new MailjetException("{$page} need a parameter");
            }
            $page = str_replace('{param}', $param, $page);
        }

        $this->initialPage = $page;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getHtml($isPassportIframe = false)
    {
        $iframeUrl = $this->getIframeUrl($isPassportIframe);

        $html = <<<HTML

<iframe
  width="100%s"
  height="100%s"
  frameborder="0" style="border:0"
  src="%s">
</iframe>

HTML;

        return sprintf($html, '%', '%', $iframeUrl);
    }

    /**
     *
     * @param mixed $parameter
     * @return boolean
     */
    private function isAllowedOnOffParameter($parameter)
    {
        if ($parameter !== self::ON && $parameter !== self::OFF) {
            return false;
        }

        return true;
    }

    /**
     *
     * @return string
     * @throws MailjetException
     */
    private function getToken()
    {
        if (!isset($_SESSION[self::SESSION_NAME])) {

            $_SESSION[self::SESSION_NAME] = $this->generateToken();
            $_SESSION[self::SESSION_SET] = time();

        } else {

            if (time() - $_SESSION[self::SESSION_SET] >= $this->sessionExpiration) {
                $_SESSION[self::SESSION_NAME] = $this->generateToken();
                $_SESSION[self::SESSION_SET] = time();
            }

        }

        return $_SESSION[self::SESSION_NAME];
    }

    /**
     *
     * @return string
     * @throws MailjetException
     */
    private function generateToken()
    {
        $params = array(
            'method' => 'JSON',
            'AllowedAccess' => $this->tokenAccess,
            'IsActive' => 'true',
            'TokenType' => 'iframe',
            'APIKeyALT' => $this->mailjetApi->getAPIKey(),
            'ValidFor' => $this->sessionExpiration,
        );

        // get the response
        $response = $this->mailjetApi->apitoken($params)->getResponse();

        if (!$response) {
            throw new MailjetException(
                "The Mailjet API does not respond."
            );
        }

        if ($response->Count <= 0) {
            throw new MailjetException(
                "The Mailjet API object not found."
            );
        }

        if (!isset($response->Data[0]->Token)) {
            throw new MailjetException(
                "The Mailjet API returned invalid response."
            );
        }

        return $response->Data[0]->Token;
    }

    /**
     *
     * @return string
     */
    private function getIframeUrl($isPassportIframe = false)
    {
        $url = $this->url . $this->initialPage . '?t=' . $this->getToken();

        if ($isPassportIframe) {
            $url .= '&lang=' . $this->locale;
        }
        else {
            $url .= '&locale=' . $this->locale;
        }

        if ($this->callback !== '') {
            $url .= '&cb=' . $this->callback;
        }

        if ($this->documentationProperties === self::OFF) {
            $url .= '&d=hide';
        }

        if ($this->newContactListCreation === self::OFF) {
            $url .= '&l=yes';
        }

        if ($this->menu === self::OFF) {
            $url .= '&show_menu=none';
        }

        if ($this->showBar === self::ON) {
            $url .= '&show_bar=yes';
        }

        if ($this->crc === self::OFF) {
            $url .= '&crc=hide';
        }

        if ($this->sp === self::ON) {
            $url .= '&sp=display';
        }

        $url .= $this->showFooter === self::OFF ? '&sftr=empty' : '';

        return $url;
    }

    /**
     *
     * @return null
     */
    private function startSession()
    {
        if (false === $this->isSessionStarted()) {
            session_start();
        }
    }

    private function isSessionStarted()
    {
        if ( php_sapi_name() !== 'cli' ) {
            if ( version_compare(phpversion(), '5.4.0', '>=') ) {
                return session_status() === PHP_SESSION_ACTIVE ? true : false;
            } else {
                return session_id() === '' ? false : true;
            }
        }
        return false;
    }

}
