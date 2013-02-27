<?php
/**
 * Zencoder API client interface.
 *
 * @category Services
 * @package  Services_Zencoder
 * @author   Michael Christopher <m@zencoder.com>
 * @version  Release: 2.1.2
 * @license  http://creativecommons.org/licenses/MIT/MIT
 * @link     http://github.com/zencoder/zencoder-php
 * @access   private
 */

function Services_Zencoder_autoload($className)
{
    if (substr($className, 0, 17) != 'Services_Zencoder') {return false;}
    $file = str_replace('_', '/', $className);
    $file = str_replace('Services/', '', $file);
    return include dirname(__FILE__) . "/$file.php";
}
spl_autoload_register('Services_Zencoder_autoload');

/**
 * Zencoder API client interface.
 *
 * @category Services
 * @package  Services_Zencoder
 * @author   Michael Christopher <m@zencoder.com>
 * @version  Release: 2.1.2
 * @license  http://creativecommons.org/licenses/MIT/MIT
 * @link     http://github.com/zencoder/zencoder-php
 */

class Services_Zencoder extends Services_Zencoder_Base
{
    const USER_AGENT = 'ZencoderPHP v2.1.2';

    /**
    * Contains the HTTP communication class
    * 
    * @var Services_Zencoder_Http
    */
    protected $http;
    /**
    * Contains the default API version
    * 
    * @var string
    */
    protected $version;

    /**
    * Provides access the Zencoder Accounts API
    * 
    * Valid functions: create, details, integration, live
    *
    * @var Services_Zencoder_Accounts
    */
    public $accounts;
    /**
    * Provides access the Zencoder Inputs API
    *
    * Valid functions: details, progress
    *
    * @var Services_Zencoder_Inputs
    */
    public $inputs;
    /**
    * Provides access the Zencoder Jobs API
    *
    * Valid functions: create, index, details, progress, resubmit, cancel
    *
    * @var Services_Zencoder_Jobs
    */
    public $jobs;
    /**
    * Provides access the Zencoder Notifications API
    *
    * Valid functions: parseIncoming
    *
    * @var Services_Zencoder_Notifications
    */
    public $notifications;
    /**
    * Provides access the Zencoder Outputs API
    *
    * Valid functions: details, progress
    *
    * @var Services_Zencoder_Outputs
    */
    public $outputs;

    /**
    * Initialize the Services_Zencoder class and sub-classes.
    *
    * @param string               $api_key      API Key
    * @param string               $api_version  API version
    * @param string               $api_host     API host
    * @param bool                 $debug        Enable debug mode
    */
    public function __construct(
        $api_key = NULL,
        $api_version = 'v2',
        $api_host = 'https://app.zencoder.com',
        $debug = false
    )
    {
        // Check that library dependencies are met
        if (strnatcmp(phpversion(),'5.2.0') < 0) {
            throw new Services_Zencoder_Exception('PHP version 5.2 or higher is required.');
        }
        if (!function_exists('json_encode')) {
            throw new Services_Zencoder_Exception('JSON support must be enabled.');
        }
        if (!function_exists('curl_init')) {
            throw new Services_Zencoder_Exception('cURL extension must be enabled.');
        }
        $this->version = $api_version;
        $this->http = new Services_Zencoder_Http(
            $api_host,
            array("curlopts" => array(
                CURLOPT_USERAGENT => self::USER_AGENT,
                CURLOPT_CAINFO => dirname(__FILE__) . "/Zencoder/zencoder_ca_chain.crt",
                ), "api_key" => $api_key, "debug" => $debug)
            );
        $this->accounts = new Services_Zencoder_Accounts($this);
        $this->inputs = new Services_Zencoder_Inputs($this);
        $this->jobs = new Services_Zencoder_Jobs($this);
        $this->notifications = new Services_Zencoder_Notifications($this);
        $this->outputs = new Services_Zencoder_Outputs($this);
    }

    /**
    * GET the resource at the specified path.
    *
    * @param string $path   Path to the resource
    * @param array  $params Query string parameters
    * @param array  $opts   Optional overrides
    *
    * @return object The object representation of the resource
    */
    public function retrieveData($path, array $params = array(), array $opts = array())
    {
        return empty($params)
            ? $this->_processResponse($this->http->get($this->_getApiPath($opts) . $path))
            : $this->_processResponse(
                $this->http->get($this->_getApiPath($opts) . $path . "?" . http_build_query($params, '', '&'))
            );
    }

    /**
    * DELETE the resource at the specified path.
    *
    * @param string $path   Path to the resource
    * @param array  $opts   Optional overrides
    *
    * @return object The object representation of the resource
    */
    public function deleteData($path, array $opts = array())
    {
        return $this->_processResponse($this->http->delete($this->_getApiPath($opts) . $path));
    }

    /**
    * POST to the resource at the specified path.
    *
    * @param string $path   Path to the resource
    * @param string $body   Raw body to post
    * @param array  $opts   Optional overrides
    *
    * @return object The object representation of the resource
    */
    public function createData($path, $body = "", array $opts = array())
    {
        $headers = array('Content-Type' => 'application/json');
        return empty($body)
            ? $this->_processResponse($this->http->post($this->_getApiPath($opts) . $path, $headers))
            : $this->_processResponse(
                $this->http->post(
                    $this->_getApiPath($opts) . $path,
                    $headers,
                    $body
                )
            );
    }

    /**
    * PUT to the resource at the specified path.
    *
    * @param string $path   Path to the resource
    * @param string $body   Raw body to post
    * @param array  $opts   Optional overrides
    *
    * @return object The object representation of the resource
    */
    public function updateData($path, $body = "", array $opts = array())
    {
        $headers = array('Content-Type' => 'application/json');
        return empty($params)
            ? $this->_processResponse($this->http->put($this->_getApiPath($opts) . $path, $headers))
            : $this->_processResponse(
                $this->http->put(
                    $this->_getApiPath($opts) . $path,
                    $headers,
                    $body
                )
            );
    }

    private function _getApiPath($opts = array())
    {
        return isset($opts['no_transform'])
            ? ""
            : "/api/" . (
                isset($opts['api_version'])
                ? $opts['api_version']
                : $this->version
            ) . "/";
    }

    private function _processResponse($response)
    {
        list($status, $headers, $body) = $response;
        if ( $status == 204 || (($status == 200 || $status == 201) && trim($body) == "")) {
            return TRUE;
        }
        if (empty($headers['Content-Type'])) {
            throw new Services_Zencoder_Exception('Response header is missing Content-Type', $body);
        }
        switch ($headers['Content-Type']) {
            case 'application/json':
            case 'application/json; charset=utf-8':
                return $this->_processJsonResponse($status, $headers, $body);
                break;
        }
        throw new Services_Zencoder_Exception(
            'Unexpected content type: ' . $headers['Content-Type'], $body);
    }

    private function _processJsonResponse($status, $headers, $body)
    {
        $decoded = json_decode($body);
        if ($status >= 200 && $status < 300) {
            return $decoded;
        }
        throw new Services_Zencoder_Exception(
            "Invalid HTTP status code: " . $status, $body
        );
    }
}
