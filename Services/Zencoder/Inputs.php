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

class Services_Zencoder_Inputs extends Services_Zencoder_Base
{
  /**
   * Return details of a specific input
   *
   * @param integer $input_id  ID of the input file you want details for
   * @param array   $params    Optional overrides
   *
   * @return Services_Zencoder_Input The object representation of the resource
   */
  public function details($input_id, $params = array()) {
    return new Services_Zencoder_Input($this->proxy->retrieveData("inputs/$input_id.json", array(), $params));
  }

  /**
   * Return progress of a specific input
   *
   * @param integer $input_id  ID of the input file you want progress for
   * @param array   $params    Optional overrides
   *
   * @return Services_Zencoder_Progress The object representation of the resource
   */
  public function progress($input_id, $params = array()) {
    return new Services_Zencoder_Progress($this->proxy->retrieveData("inputs/$input_id/progress.json", array(), $params));
  }
}
