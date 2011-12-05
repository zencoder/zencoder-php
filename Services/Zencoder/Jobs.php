<?php
/*

  Zencoder API PHP Library
  Version: 2.0
  See the README file for info on how to use this library.

*/

class Services_Zencoder_Jobs extends Services_Zencoder_Base {
  public $id, $test, $state, $outputs = array();

  public function create($params = NULL) {
    if(is_string($params)) {
      $json = trim($params);
    } else if(is_array($params)) {
      $json = json_encode($params);
    } else {
      throw new Services_Zencoder_Exception(
        'Job parameters required to create job.');
    }
    return $request = $this->proxy->createData("jobs", $json);
  }

  public function index() {
    return $this->proxy->retrieveData("jobs");
  }

  public function details($job_id) {
    return $this->proxy->retrieveData("jobs/$job_id");
  }

  public function progress($job_id) {
    return $this->proxy->retrieveData("jobs/$job_id/progress");
  }

  public function resubmit($job_id) {
    return $this->proxy->updateData("jobs/$job_id/resubmit");
  }

  public function cancel($job_id) {
    return $this->proxy->updateData("jobs/$job_id/cancel");
  }

  public function delete($job_id) {
    return $this->proxy->deleteData("jobs/$job_id");
  }
}
