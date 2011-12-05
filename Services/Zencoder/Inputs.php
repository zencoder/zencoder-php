<?php
/*

  Zencoder API PHP Library
  Version: 2.0
  See the README file for info on how to use this library.

*/

class Services_Zencoder_Inputs extends Services_Zencoder_Base {
  public function details($input_id) {
    return $this->proxy->retrieveData("inputs/$input_id");
  }

  public function progress($input_id) {
    return $this->proxy->retrieveData("inputs/$input_id/progress");
  }
}
