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

class Services_Zencoder_Accounts extends Services_Zencoder_Base
{
  /**
   * Create a Zencoder account
   *
   * @param array  $account Array of attributes to use when creating the account
   * @param array  $params  Optional overrides
   *
   * @return Services_Zencoder_Account The object representation of the resource
   */
  public function create($account = NULL, $params = array()) {
    if(is_string($account)) {
      $json = trim($account);
    } else if(is_array($account)) {
      $json = json_encode($account);
    } else {
      throw new Services_Zencoder_Exception(
        'Account parameters required to create account.');
    }
    return new Services_Zencoder_Account($this->proxy->createData("account", $json, $params));
  }

  /**
   * Return details of your Zencoder account
   *
   * @param array  $params  Optional overrides
   *
   * @return Services_Zencoder_Account The object representation of the resource
   */
  public function details($params = array()) {
    return new Services_Zencoder_Account($this->proxy->retrieveData("account.json", array(), $params));
  }

  /**
   * Put your account into integration mode
   *
   * @param array  $params  Optional overrides
   *
   * @return bool If the operation was successful
   */
  public function integration($params = array()) {
    return $this->proxy->updateData("account/integration", "", $params);
  }

  /**
   * Put your account into live mode
   *
   * @param array  $params  Optional overrides
   *
   * @return bool If the operation was successful
   */
  public function live($params = array()) {
    return $this->proxy->updateData("account/live", "", $params);
  }
}
