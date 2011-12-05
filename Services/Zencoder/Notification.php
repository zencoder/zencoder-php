<?php
/*

  Zencoder API PHP Library
  Version: 2.0
  See the README file for info on how to use this library.

*/

class Services_Zencoder_Notification {
  public $output, $job;

  public function __construct($params) {
    if(!empty($params["output"])) $this->output = new Services_Zencoder_Output($params["output"]);
    if(!empty($params["job"])) $this->job = new Services_Zencoder_Job($params["job"], array("build" => true));
  }
}
