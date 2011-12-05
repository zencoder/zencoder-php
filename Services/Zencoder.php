<?php
/*

  Zencoder API PHP Library
  Version: 2.0
  See the README file for info on how to use this library.

*/

class Services_Zencoder_Exception extends ErrorException {}

function Services_Zencoder_autoload($className) {
    if (substr($className, 0, 17) != 'Services_Zencoder') {
        return false;
    }
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
 * @license  http://creativecommons.org/licenses/MIT/MIT
 * @link     http://github.com/zencoder/zencoder-php
 */
class Services_Zencoder extends Services_Zencoder_Base
{
  const USER_AGENT = 'ZencoderPHP v2.0';

  protected $http;
  protected $version;

  /**
   * Constructor.
   *
   * @param string               $api_key      API Key
   * @param string               $api_version  API version
   * @param string               $api_host     API host
   */
  public function __construct(
      $api_key = NULL,
      $api_version = 'v2',
      $api_host = 'https://app.zencoder.com'
  ) {
    $this->version = $api_version;
    $this->http = new Services_Zencoder_Http(
        $api_host,
        array("curlopts" => array(
            CURLOPT_USERAGENT => self::USER_AGENT,
            CURLOPT_CAINFO => dirname(__FILE__) . "/zencoder_ca_chain.crt",
        ), "api_key" => $api_key)
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
   *
   * @return object The object representation of the resource
   */
  public function retrieveData($path, array $params = array())
  {
    $path = "/api/$this->version/$path.json";
    return empty($params)
        ? $this->_processResponse($this->http->get($path))
        : $this->_processResponse(
            $this->http->get("$path?" . http_build_query($params, '', '&'))
        );
  }

  /**
   * DELETE the resource at the specified path.
   *
   * @param string $path   Path to the resource
   * @param array  $params Query string parameters
   *
   * @return object The object representation of the resource
   */
  public function deleteData($path)
  {
    $path = "/api/$this->version/$path.json";
    return $this->_processResponse($this->http->delete($path));
  }

  /**
   * POST to the resource at the specified path.
   *
   * @param string $path   Path to the resource
   * @param array  $params Query string parameters
   *
   * @return object The object representation of the resource
   */
  public function createData($path, $body = "")
  {
    $path = "/api/$this->version/$path";
    $headers = array('Content-Type' => 'application/json');
    return empty($body)
        ? $this->_processResponse($this->http->post($path, $headers))
        : $this->_processResponse(
            $this->http->post(
                $path,
                $headers,
                $body
            )
        );
  }

  /**
   * PUT to the resource at the specified path.
   *
   * @param string $path   Path to the resource
   * @param array  $params Query string parameters
   *
   * @return object The object representation of the resource
   */
  public function updateData($path, $body = "")
  {
    $path = "/api/$this->version/$path";
    $headers = array('Content-Type' => 'application/json');
    return empty($params)
        ? $this->_processResponse($this->http->put($path, $headers))
        : $this->_processResponse(
            $this->http->put(
                $path,
                $headers,
                $body
            )
        );
  }

  private function _processResponse($response)
  {
    list($status, $headers, $body) = $response;
    if ($status == 204) {
        return TRUE;
    }
    if (empty($headers['Content-Type'])) {
        throw new Services_Zencoder_Exception('Response header is missing Content-Type');
    }
    switch ($headers['Content-Type']) {
      case 'application/json':
      case 'application/json; charset=utf-8':
          return $this->_processJsonResponse($status, $headers, $body);
          break;
    }
    throw new Services_Zencoder_Exception(
        'Unexpected content type: ' . $headers['Content-Type']);
  }

  private function _processJsonResponse($status, $headers, $body) {
    $decoded = json_decode($body);
    if ($status >= 200 && $status < 300) {
        return $decoded;
    }
    throw new Services_Zencoder_Exception(
        (int)$decoded->status,
        $decoded->message,
        isset($decoded->code) ? $decoded->code : null,
        isset($decoded->more_info) ? $decoded->more_info : null
    );
  }
}
