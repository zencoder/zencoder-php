<?php
/*

  Zencoder API PHP Library
  Version: 2.0
  See the README file for info on how to use this library.

*/

class Services_Zencoder_Accounts extends Services_Zencoder_Base {
  public function create($params = NULL) {
    if(is_string($params)) {
      $json = trim($params);
    } else if(is_array($params)) {
      $json = json_encode($params);
    } else {
      throw new Services_Zencoder_Exception(
        'Account parameters required to create account.');
    }
    return $this->proxy->createData("account", $json);
  }

  public function details() {
    return $this->proxy->retrieveData("account");
  }

  public function integration() {
    return $this->proxy->updateData("account/integration");
  }

  public function live() {
    return $this->proxy->updateData("account/live");
  }
}
