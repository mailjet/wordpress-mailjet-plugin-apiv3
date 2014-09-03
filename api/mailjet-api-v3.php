<?php

/**
 * Mailjet Public API / The real-time Cloud Emailing platform
 *
 * Connect your Apps and Make our product yours with our powerful API
 * http://www.mailjet.com/ Mailjet SAS Website
 *
 * @package		API v0.3
 * @author		David Coullet
 * @author		Mailjet Dev team
 * @copyright	Copyright (c) 2012-2013, Mailjet SAS, http://www.mailjet.com/Terms-of-use.htm
 * @file
 */

// ---------------------------------------------------------------------

/**
 * Mailjet Public API Main Class
 *
 * This class enables you to connect your Apps and use our powerful API.
 * You can use the 'metadata' call to retrieve a list of each object available
 * or implemented a live discovery.
 * http://www.mailjet.com/docs/api
 *
 * updated on 2013-09-03
 *
 * @class		MailjetApi
 * @author		David Coullet
 * @author		Mailjet Dev team
 * @version		0.1
 */
class WP_Mailjet_Api_V3
{
    /**
     * Mailjet API Key to use.
     * You can edit directly and add here your Mailjet infos
     *
     * @access	private
     * @var		string $_apiKey
     */
    private $_apiKey = '';

    /**
     * Mailjet API Secret Key to use.
     * You can edit directly and add here your Mailjet infos
     *
     * @access	private
     * @var		string $_secretKey
     */
    private $_secretKey = '';

    /**
     * Seconds before updating the cache object
     * If set to 0, Object caching will be disabled
     *
     * @access	private
     * @var		integer $_cache
     */
    private $_cache = 0;//600;

// ---------------------------------------------------------------------

    /**
     * API URL
     *
     * @access	private
     * @var		string
     */
    private $_apiUrl = 'api.mailjet.com/v3/';

    /**
     * API version to use
     *
     * @access	private
     * @var		string
     */
    private $_version = 'REST';

    /**
     * Debug internal flag
     *
     * @access	private
     * @var		boolean
     */
    private $_debug = false;

    /**
     * Debug Label
     *
     * @access	private
     * @var		string
     */
    private $_debug_info = '';

    /**
     * Debug buffer copy
     *
     * @access	private
     * @var		string
     */
    private $_buffer = '';

    /**
     * Debug method copy
     *
     * @access	private
     * @var		string
     */
    private $_method = '';

    /**
     * Debug by cURL
     *
     * @access	private
     * @var		array
     */
    private $_info = NULL;

    /**
     * cURL handle resource
     *
     * @access	private
     * @var		resource
     */
    private $_curl_handle = NULL;

    /**
     * 
     * @var Boolean
     */
    protected $_log_allowed = false;
    
    /**
     * 
     * @var Boolean
     */
    protected $_log = false;
    
    /**
     * 
     * @var Boolean
     */
    protected $_log_once = false;
    
    /**
     *
     * @var Boolean
     */
    protected $_extra_err = false;
    
    /**
     * 
     * @var String
     */
    protected $_log_path = 'logs/api/current.log';
    
    /**
     * Singleton pattern : Current instance
     *
     * @access	private
     * @var		resource
     */
    private static $_instance = NULL;

    /**
     * Constructor
     *
     * Set $_apiKey and $_secretKey if provided & Update $_apiUrl with protocol
     *
     * @access	public
     * @uses	MailjetApi::$_apiKey
     * @uses	MailjetApi::$_secretKey
     * @param string  $_apiKey    Mailjet API Key
     * @param string  $_secretKey Mailjet API Secret Key
     * @param boolean $secure     TRUE to secure the transaction, FALSE otherwise
     */
    public function __construct($apiKey = NULL, $secretKey = NULL, $secure = FALSE)
    {
        if (isset($apiKey))
            $this->_apiKey = $apiKey;
        if (isset($secretKey))
            $this->_secretKey = $secretKey;
        $this->_apiUrl = 'http://'.$this->_apiUrl.$this->_version.'/';
        $this->secure($secure);
        
        $this->_log_allowed = get_cfg_var('MAILJET_ENVIRONMENT') == 'dev';
        
        $this->_fixLogPath();
    }

    /**
     * Singleton pattern :
     * Get the instance of the object if it already exists
     * or create a new one.
     *
     * @access	public
     * @uses	MailjetApi::$_instance
     */
    public static function getInstance()
    {
        if (!(self::$_instance instanceof self))
            self::$_instance = new self();

        return self::$_instance;
    }

    /**
     * Destructor
     *
     * Close the cURL handle resource
     *
     * @access	public
     * @uses	MailjetApi::$_curl_handle
     */
    public function __destruct()
    {
        if(!is_null($this->_curl_handle))
            curl_close($this->_curl_handle);
        $this->_curl_handle = NULL;
    }

    /**
     * Set new Api Key and Secret Key
     *
     * @access	public
     * @uses	MailjetApi::$_apiKey
     * @uses	MailjetApi::$_secretKey
     * @param string $apiKey    Mailjet API Key
     * @param string $secretKey Mailjet API Secret Key
     */
    public function setKeys($apiKey, $secretKey)
    {
        $this->_apiKey = $apiKey;
        $this->_secretKey = $secretKey;
    }

    /**
     * Set the seconds before updating the cache object
     * If set to 0, Object caching will be disabled
     *
     * @access	public
     * @uses	MailjetApi::$_cache
     * @param integer $cache Cache to set in seconds
     */
    public function setCache($cache)
    {
        $this->_cache = $cache;
    }

    /**
     * Get the seconds before updating the cache object
     * If set to 0, Object caching will be disabled
     *
     * @access	public
     * @uses	MailjetApi::$_cache
     *
     * @return integer Cache in seconds
     */
    public function getCache()
    {
        return ($this->_cache);
    }

    /**
     * 
     * @param Boolean $flag
     * @return mailjetapi
     */
    public function setLog($flag)
    {
    	$this->_log = (boolean) $flag;
    	return $this;
    }
    
    /**
     * 
     * @return boolean
     */
    public function getLog()
    {
    	return $this->_log;
    }
    
    /**
     * 
     * @param Boolean $flag
     * @return mailjetapi
     */
    public function setLogOnce($flag)
    {
    	$this->_log_once = (boolean) $flag;
    	return $this;
    }
    
    /**
     * 
     * @return boolean
     */
    public function getLogOnce()
    {
    	return $this->_log_once;
    }
    
    /**
     *
     * @param Boolean $flag
     * @return mailjetapi
     */
    public function setExtraError($flag)
    {
    	$this->_extra_err = (boolean) $flag;
    	return $this;
    }
    
    /**
     *
     * @return boolean
     */
    public function getExtraError()
    {
    	return $this->_extra_err;
    }

    /**
     * Enable log only if MAILJET_ENVIRONMENT == 'dev'
     * @return boolean
     */
    public function logAllowed()
    {
    	return $this->_log_allowed;
    }
    
    /**
     * 
     * @param String $path
     * @return mailjetapi
     */
    public function setLogPath($path)
    {
    	if ($real_path = realpath($path)) {
    		$this->_log_path = $real_path;
    	}
    	return $this;
    }
    
    /**
     * 
     * @return String
     */
    public function getLogPath()
    {
    	return $this->_log_path;
    }
    
    /**
     * 
     * @return mailjetapi
     */
    protected function _fixLogPath()
    {
    	//fix log path
		if(!defined('SYSTEMPATH')) define('SYSTEMPATH', dirname(__FILE__).'/../');
    	$this->_log_path = realpath(SYSTEMPATH.$this->_log_path);
    	$logFilename = 'daily-'.date('Ymd').'.log';
    	$this->_log_path = str_replace('current.log', $logFilename, $this->_log_path);
		return $this;
    }
    
    /**
     * Secure or not the transaction through https
     *
     * @access	public
     * @uses	MailjetApi::$_apiUrl
     * @param boolean $secure TRUE to secure the transaction, FALSE otherwise
     */
    public function secure($secure = TRUE)
    {
        $protocol = 'http';
        if ($secure)
            $protocol = 'https';
        $this->_apiUrl = preg_replace('/http(s)?:\/\//', $protocol.'://', $this->_apiUrl);
    }

    /**
     * Make the magic call ;)
     *
     * Check for some arguments and order them before sending the request.
     * If '_debug_info' is found, some data are stored and can be retrieved
     * with a call to MailjetApi::getDebugInfo().
     *
     * @access	public
     * @uses	MailjetApi::$_debug
     * @uses	MailjetApi::$_debug_info
     * @uses	MailjetApi::sendRequest() to send the request
     * @param string $object Method to call
     * @param array  $args   Array of parameters
     *
     * @return string JSON string by default (format can be change with the 'format' parameter)
     */
    public function __call($object, $args)
    {
        if (sizeof($args) > 0)
            $params = $args[0];
        else
            $params = array();

        if (isset($params['method'])) {
            $method = strtoupper($params['method']);
            unset($params['method']);
        }
        if (!isset($method) || !in_array($method, array('GET', 'POST', 'PUT', 'DELETE', 'JSON')))
            $method = 'GET';

        if (isset($params['debug_info'])) {
            $this->_debug = TRUE;
            $this->_debug_info = $params['debug_info'];
            unset($params['debug_info']);
        }
        
        /**
         * Logging
         */
        if (isset($params['log'])) {
        	$this->setLog($params['log']);
        	unset($params['log']);
        }
        
        if (isset($params['log_once'])) {
        	$this->setLogOnce($params['log_once']);
        	unset($params['log_once']);
        }
        
        if (isset($params['log_path'])) {
        	$this->setLogPath($params['log_path']);
        	unset($params['log_path']);
        }
        
        // Extra Error
        if (isset($params['extra_err'])) {
        	$this->setExtraError($params['extra_err']);
        	unset($params['extra_err']);
        }
        
        /**
         * Fallback logging when debug is true
         */
        if ($this->_debug) {
        	$this->setLogOnce(true);
        }

        return (json_decode($this->sendRequest($object, $params, $method)));
    }

    /**
     * Send Request
     *
     * Send the request to the Mailjet API server and get back the result
     * Basically, setup and execute the curl process.
     * Cache management added
     *
     * @access	private
     * @uses	MailjetApi::$_info
     * @uses	MailjetApi::$_debug
     * @uses	MailjetApi::$_buffer
     * @uses	MailjetApi::$_method
     * @uses	MailjetApi::$_apiKey
     * @uses	MailjetApi::$_secretKey
     * @uses	MailjetApi::$_curl_handle
     * @uses	MailjetApi::buildURL() to build the full Url for the request and update the list of parameters accordingly
     * @param string $object Object or collection of resources you want to access
     * @param array  $params Additional parameters for the request
     * @param string $method POST:	Create a resource
     * 							GET:	Read one or multiple resources
     * 							PUT:	Update one or multiple resources
     * 							DELETE:	Delete one or multiple resources
     *
     * @return string the result of the request
     */
    private function sendRequest($object, $params, $method)
    {

    	// Log if this is a JSON call with an ID inside params
    	$is_json_put = (isset($params['ID']) && !empty($params['ID']));
    
        list($url, $params) = $this->buildURL($object, $params, $method);

        if ($this->_cache != 0 && $method == 'GET' && !$this->_akid) {
            $file = $method.'.'.$object.'.'.hash('md5', $this->_apiKey.http_build_query($params, '', '')).'.cache';
        }

        if(is_null($this->_curl_handle))
            $this->_curl_handle = curl_init();

        curl_setopt($this->_curl_handle, CURLOPT_URL, $url);
        curl_setopt($this->_curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->_curl_handle, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($this->_curl_handle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($this->_curl_handle, CURLOPT_USERPWD, $this->_apiKey.':'.$this->_secretKey);
        curl_setopt($this->_curl_handle, CURLOPT_CUSTOMREQUEST, $method);

        switch ($method) {
            case 'GET' :
                curl_setopt($this->_curl_handle, CURLOPT_HTTPGET, TRUE);
                curl_setopt($this->_curl_handle, CURLOPT_POSTFIELDS, NULL);
            break;

            case 'POST':
                if($params['Action']=='Add'){
                    curl_setopt($this->_curl_handle, CURLOPT_POST, count($params));
                    curl_setopt($this->_curl_handle, CURLOPT_POSTFIELDS, json_encode($params));
                    curl_setopt($this->_curl_handle, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                }
                else{
                    curl_setopt($this->_curl_handle, CURLOPT_POST, count($params));
                    curl_setopt($this->_curl_handle, CURLOPT_POSTFIELDS, $this->curl_build_query($params));
                }
            break;

            case 'PUT':
                curl_setopt($this->_curl_handle, CURLOPT_POSTFIELDS, $this->curl_build_query($params));
            break;
            
            case 'JSON':
            	if($is_json_put)
            		curl_setopt($this->_curl_handle, CURLOPT_CUSTOMREQUEST, "PUT");            		
            	else
            		curl_setopt($this->_curl_handle, CURLOPT_CUSTOMREQUEST, "POST");
            	
            	$params = json_encode($params);
				curl_setopt($this->_curl_handle, CURLOPT_POSTFIELDS, $params);
				curl_setopt($this->_curl_handle, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($this->_curl_handle, CURLOPT_HTTPHEADER, array(
				    'Content-Type: application/json',
				    'Content-Length: ' . strlen($params))
				);
            break;
            	
        }

        $buffer = curl_exec($this->_curl_handle);

        $this->_info = curl_getinfo($this->_curl_handle);
        $this->_response_code = $this->_info['http_code'];
        
		curl_close($this->_curl_handle);
		$this->_curl_handle = null;
        
        if ($this->_debug) {
            $this->_buffer = $buffer;
            $this->_method = $method;
            $this->_debug = FALSE;
        }

        if ($this->_cache != 0 && $method == 'GET' && !$this->_akid) {
            $data = array('timestamp' => time(), 'result' => $buffer, 'http_code' => $this->_info['http_code']);
        }
        
        if( $this->getExtraError() ){
        	$this->getExtraErr( $buffer );
        	$this->setExtraError( false );
        }
        
        if ($this->logAllowed() && ($this->getLog() || $this->getLogOnce())) {

        	if ($this->_info) {
	        	$moreInfo = print_r(array(
	       			'content_type'	=> $this->_info['content_type'],
	        		'http_code'		=> $this->_info['http_code'],
	        		'total_time'	=> $this->_info['total_time'],
	        	), true);
	        	$moreInfo = substr($moreInfo, 6);
        	} else {
        		$moreInfo = 'none';
        	}
        	
        	$date = DateTime::createFromFormat('U.u', microtime(true));
        	
            if (is_array($params)) {
        		$jsonParams = json_encode($params);
        	} else {
        		$jsonParams = $params;
        	}
        	        	
        	$logLines = array();
        	
			$logLines[] = '';
        	$logLines[] = $date->format('Y-m-d H:i:s.u')." > {$object} :: {$method}";
			$logLines[] = '';
			$logLines[] = $this->_info['url'];
			$logLines[] = '';
			$logLines[] = "Request";
			$logLines[] = $jsonParams;
			$logLines[] = '';
			$logLines[] = "Response";
			$logLines[] = $buffer;
			$logLines[] = '';
			$logLines[] = 'Info';
			$logLines[] = $moreInfo;
			$logLines[] = '';
			$logLines[] = str_repeat("=", 100);
			$logLines[] = '';
				
			$logText = implode(PHP_EOL, $logLines);

			file_put_contents($this->getLogPath(), $logText, FILE_APPEND);
        	
			$this->setLogOnce(false);
			
        }
        
        return $buffer;
    }

    /**
     * Build the full Url for the request and update the parameters if needed
     *
     * @access	private
     * @uses	MailjetApi::$_apiUrl
     * @param string $object Object or collection of resources you want to access
     * @param array  $params Additional parameters for the request
     * @param string $method POST:	Create a resource
     * 							GET:	Read one or multiple resources
     * 							PUT:	Update one or multiple resources
     * 							DELETE:	Delete one or multiple resources
     *
     * @return array Full built Url for the request and new params
     */
    private function buildURL($object, $params, $method = 'GET')
    {
        $url = $this->_apiUrl.$object;

        if (isset($params['ID'])) {
            $url .= '/'.$params['ID'];
            unset($params['ID']);
        }

        $this->_akid = array_key_exists('akid', $params);

        if ($method == 'GET')
            $url .= '?'.http_build_query($params, '', '&');
        elseif ($method == 'PUT' || $method == 'POST' || $method == 'DELETE' || $method == 'JSON') {
            $tocheck = array('format', 'style', 'countrecords', 'recurse', 'akid', 'DuplicateFrom');
            $query = array();
            foreach ($params as $key => $value)
                if (in_array($key, $tocheck)) {
                    $query[$key] = $value;
                    unset($params[$key]);
                }
            if(count($query))
            	$url .= '?'.http_build_query($query, '', '&');
            	
        } 

        return (array($url, $params));
    }

    /**
     * Build query for cURL
     * Beware of the Boolean !
     *
     * @access	private
     * @param array $params Post parameters for the request
     *
     * @return string URL-encoded query string from the associative array provided
     */
    private function curl_build_query($params)
    {
    	foreach($params as $key => $value) {
	    	if($value === TRUE)
	    		$value = 'true';
	    	elseif($value === FALSE)
	    		$value = 'false';
    	}
        /*array_walk($params, function(&$value, &$key) {
            if ($value === TRUE) {
                $value = 'true';
            } elseif ($value === FALSE) {
                $value = 'false';
            }
        });*/

        return (http_build_query($params, '', '&'));
    }

    /**
     * Get the last HTTP code retrieved by cURL
     *
     * Warning : Information returned by this function is kept.
     * So, if you call it again, the previous info is returned.
     *
     * @access	public
     * @uses	MailjetApi::$_info
     *
     * @return integer last HTTP code retrieved by cURL or 0 if not set
     */
    public function getLastHTTPCode()
    {
        if (isset($this->_info['http_code']))
            return ($this->_info['http_code']);

        return (0);
    }

    /**
     * Set some info if the object came from the cache
     *
     * @access	private
     * @uses	MailjetApi::$_info
     * @uses	MailjetApi::$_buffer
     * @uses	MailjetApi::$_method
     * @uses	MailjetApi::$_debug_info
     */
    private function setCacheDebugInfo($method, $url, $data)
    {
        $this->_debug_info .= ' /* LAST CACHED AT '.date('Y/m/d H:i:s', $data['timestamp']).' - UPDATING EVERY '.$this->_cache.'s */';
        $this->_buffer = $data['result'];
        $this->_method = $method;
        $this->_info = array();
        $this->_info['url'] = $url;
        $this->_info['http_code'] = $data['http_code'];
        $this->_info['total_time'] = $this->_info['pretransfer_time'] = 0;
        $this->_debug = FALSE;
    }

    /**
     * Get some info for debugging purpose
     *
     * Warning : Information returned by this function is kept.
     * So, if you call it again, the previous info is returned.
     * To update this array, you need to add the key 'debug_info'
     * to the list of parameters. You can specified a value to
     * identify the returned array.
     *
     * @access	public
     * @uses	MailjetApi::$_info
     * @uses	MailjetApi::$_method
     * @uses	MailjetApi::$_debug_info
     *
     * @return array with some debug info
     */
    public function getDebugInfo()
    {
        $status_code = array (
            200 => 'OK - Everything went fine.',
            201 => 'OK - Created : The POST request was successfully executed.',
            204 => 'OK - No Content : The Delete request was successful.',
            304 => 'OK - Not Modified : The PUT request didn’t affect any record.',
            400 => 'KO - Bad Request : Please check the parameters.',
            401 => 'KO - Unauthorized : A problem occurred with the apiKey/secretKey. You may be not authorized to access the API or your apiKey may have expired.',
            403 => 'KO - Forbidden : You are not authorized to call that function.',
            404 => 'KO - Not Found : The resource with the specified ID does not exist.',
            405 => 'KO - Method not allowed : Attempt to put/post multiple resources in 1 request.',
            500 => 'KO - Internal Server Error.',
            503 => 'KO - Service unavailable.'
        );

        if (array_key_exists($this->_info['http_code'], $status_code))
            $http_code_text = $status_code[$this->_info['http_code']];
        else
            $http_code_text = 'KO - Service unavailable.';

        $status_message = '';
        if ($this->_info['http_code'] >= 400) {
            $buffer = json_decode($this->_buffer);
            if (!is_null($buffer) && isset($buffer->StatusCode) && isset($buffer->ErrorMessage)) {
                $status_message = $buffer->StatusCode.' - '.$buffer->ErrorMessage;
                if (isset($buffer->ErrorInfo) && !empty($buffer->ErrorInfo))
                    $status_message .= ' ('.$buffer->ErrorInfo.')';
            }
        }

        $res = array(
            'debug_info'	=> $this->_debug_info,
            'method'		=> $this->_method,
            'url'			=> $this->_info['url'],
            'duration'		=> $this->_info['total_time'] - $this->_info['pretransfer_time'],
            'http_code'		=> $this->_info['http_code'],
            'http_code_text'=> $http_code_text,
            'status_message'=> $status_message,
            'curl_info'		=> $this->_info,
            'buffer'		=> $this->_buffer
        );

        return $res;
    }
    
    protected function getExtraErr( &$buffer )
    {   
    	$bufferCheck = json_decode($buffer);
    	
    	if( is_null( $bufferCheck ) )
    		return;
    	
    	$buffer = $bufferCheck;   	
    	
    	if( $this->_info['http_code'] < 400 )
    	{
    		$response = array( (object)array( "ExtraErrCode" => $this->_info['http_code'], "ExtraErrKey" => "", "ExtraErrMsg" => "" ) );
    		$buffer->ExtraError = $response ;
    		$buffer = json_encode( $buffer );
    		return;
    	}
    	
    	$internalErrors = array(  
    			'MJ01' => 'Could not determine APIKey', 								// SERRCouldNotDetermineAPIKey
    			'MJ02' => 'No persister object found for class: "%s"', 					// SErrNoPersister
    			'MJ03' => 'A non-empty value is required', 								// SErrValueRequired
    			'MJ04' => 'Value must have at least length %d',							// SErrMinLength
    			'MJ05' => 'Value may have at most length %d',							// SErrMaxLength
    			'MJ06' => 'Value must be larger than or equal to %s',					// SErrMinValue
    			'MJ07' => 'Value must be less than or equal to %s',						// SErrMaxValue
    			'MJ08' => 'Property %s is invalid: %s', 								// SErrInProperty
    			'MJ09' => 'Value is not in list of allowed values: (%s)',				// SErrValueNotInList
    			'MJ10' => 'Value must be positive',										// SErrPositiveValueRequired
    			'MJ11' => 'Unknown object type "%s".',									// SErrUnknownObject
    			'MJ12' => 'Cannot save object of type %s',								// SerrCannotSaveObjectType
    			'MJ13' => 'Invalid characters in MD5 hash: "%s"',						// SErrInvalidHashCharacters
    			'MJ14' => 'Invalid length for MD5 hash: %d',							// SErrInvalidHashLength
    			'MJ15' => 'Unknown relation name : "%s"',								// SErrUnkownRelation
    			'MJ16' => 'Class "%s" does not support a unique key.',					// SErrNoAlternateKey
    			'MJ17' => '(%s) Cannot search unique key: unique key value is empty.',	// SErrNoAlternateKeyValue
    			'MJ18' => 'A %s resource with value "%s" for %s already exists.',		// SErrDuplicateKey
    			'MJ19' => 'Setting a value for property "%s" is not allowed',			// SErrCannotWriteProperty
    			'MJ20' => 'Setting a value for properties is not allowed',				// SErrCannotWriteProperties   			
    			// ContactMetadata
    			'CM01' => 'Property "%s" already exists',								// sERRCMPropertyAlreadyExists
    			'CM02' => 'Unknown namespace : %s',										// SERRCMUnknownNamespace
    			'CM03' => '"%s" is not a valid integer value for key %s',				// SERRCMNotAValidIntegerValueForKey
    			'CM04' => '"%s" is not a valid bool value for key %s',					// SERRCMNotAValidBoolValueForKey
    			'CM05' => '"%s" is not a valid float value for key %s',					// SERRCMNotAValidFloatValue
    			'CM06' => 'Length of value (%d bytes) exceeds maximum data length (%d bytes) ',	// SERRCMLengthOfValueExceedsMaxDataLength
    			'CM07' => 'Internal error: invalid data type %d',						// SERRCMInternalErrorInvalidDataType
    			'CM08' => '"%s" is not a valid datatype'								// SERRCMNotAValidDataType
    	); 	
    	
    	$response = array();
    	
    		// We expect here $this->_info['http_code']  to be >= 400   	
    	if ( isset($buffer->StatusCode) ) {
    		if( ( isset($buffer->ErrorInfo) && !empty($buffer->ErrorInfo) ) || ( isset($buffer->ErrorMessage) && !empty($buffer->ErrorMessage) ) )
    		{	
    			$comeFromString = false;
    			if( !empty( $buffer->ErrorInfo ) )
    			{  	
    				$info = json_decode( $buffer->ErrorInfo );
    				if( empty( $info) ) // message is type=string
    				{   	
    					$comeFromString = true ;
    					$errInfos = array( (object)$buffer->ErrorInfo );
    				}else{   					
    					$errInfos =  $info;
    				}
    			}else{  // ( !empty( $buffer->ErrorMessage ) )
    				$info = json_decode( $buffer->ErrorMessage );
    				if( empty( $info ) ) // message is type=string
    				{
    					$comeFromString = true ;
    					$errInfos = array( (object)$buffer->ErrorMessage );
    				}else{   					
    					$errInfos = $info;
    				}
    			}	
    			/**
    			*  Default response. Most of the ErrorInfo/ErrorMessage will come as they are - without specific err code in front!
    			*  We keep the ExtraErrors = ErrorInfo/ErrorMessage. 
    			*  
    			*  In the feature all the responses will consist these special codes.
    			*  When this happen (!!! ToDo !!!) -> we have to override coming StatusCode and ErrorInfo with parsed error results. And remove this ExtraError from the buffer
    			*  $internalErrors array and preg_match -> should be removed. We should catch the first 4 symbols from the string (this will be StatusCode), 
    			*  all the remaining string will be ErrorInfo 
    			*/  			   			 			 			    			
    			$errCodes = array_keys( $internalErrors );   			
    			$regexp = '/^(' . implode( '|',array_values( $errCodes ) ).')/i';   
    			foreach( $errInfos as $errInfoObj )
    			{    
    				foreach( $errInfoObj as $key => $errInfo )	
    				{			
	    				$tempResp = array();
	    				$tempResp["ExtraErrCode"] = $buffer->StatusCode;
	    				$tempResp["ExtraErrKey"] = "";
	    				$tempResp["ExtraErrMsg"] = $errInfo;
	    				
	    				preg_match($regexp, $errInfo, $matches);   	
	    				if( !empty( $matches ))
	    				{
	    					$tempResp["ExtraErrCode"] = $matches[1];
	    					if( $comeFromString )
	    						$tempResp["ExtraErrKey"] = '';
	    					else 
	    						$tempResp["ExtraErrKey"] = $key;
	    					$tempResp["ExtraErrMsg"] = $internalErrors[$matches[1]];
	    				}
	    				$response[] = $tempResp ;
    				}
    			}   			 			   			
    		}
    	}
    		// Check if is $response empty !    
    	if( empty( $response ) )
    	{
    		$response = array( (object)array( "ExtraErrCode" => $this->_info['http_code'], "ExtraErrKey" => "", "ExtraErrMsg" => "" ) );
    	}	
    	
    	$buffer->ExtraError = $response ;
    	$buffer = json_encode( $buffer );
    }

}