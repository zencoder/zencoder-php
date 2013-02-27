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
 */

abstract class Services_Zencoder_Base
    implements Services_Zencoder_HttpProxy
{

  protected $proxy;

  public function __construct(Services_Zencoder_HttpProxy $proxy)
  {
    $this->proxy = $proxy;
  }

  public function createData($path, $body = "", array $opts = array())
  {
      return $this->proxy->createData($path, $body, $opts);
  }

  public function retrieveData($path, array $params = array(), array $opts = array())
  {
      return $this->proxy->retrieveData($path, $params, $opts);
  }

  public function updateData($path, $body = "", array $opts = array())
  {
      return $this->proxy->updateData($path, $body, $opts);
  }

  public function deleteData($path, array $opts = array())
  {
      return $this->proxy->deleteData($path, $opts);
  }
}
