<?php
/*

  Zencoder API PHP Library
  Version: 2.0
  See the README file for info on how to use this library.

*/

class Services_Zencoder_Notifications extends Services_Zencoder_Base {
  public function parseIncoming() {
    $incoming_data = json_decode(trim(file_get_contents('php://input')), true);
    if (!$incoming_data) {
      throw new Services_Zencoder_Exception(
        'Unable to parse notification data: ' . file_get_contents('php://input'));
    }
    return new Services_Zencoder_Notification();
  }
}
