<?php
/*

  Zencoder API PHP Library
  Version: 2.0
  See the README file for info on how to use this library.

*/

class Services_Zencoder_Outputs extends Services_Zencoder_Base {
  public $id, $label, $url, $state, $error_message, $error_link;

  public function details($output_id) {
    return $this->proxy->retrieveData("outputs/$output_id");
  }

  public function progress($output_id) {
    return $this->proxy->retrieveData("outputs/$output_id/progress");
  }
}
