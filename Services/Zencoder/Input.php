<?php
/*

  Zencoder API PHP Library
  Version: 2.0
  See the README file for info on how to use this library.

*/

class Services_Zencoder_Input {
  public $id;

  public function __construct($params) {
    $this->update_attributes($params);
  }

  private function update_attributes($attributes = array()) {
    foreach($attributes as $attr_name => $attr_value) {
      if(!function_exists($this->$attr_name)) {
        $this->$attr_name = $attr_value;
      }
    }
  }
}
