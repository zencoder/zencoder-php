<?php
/*

  Zencoder API PHP Library
  Version: 2.0
  See the README file for info on how to use this library.

*/

class Services_Zencoder_Job {
  public $id, $test, $state, $outputs = array();
  protected $raw_response;

  public function __construct($params) {
    $this->raw_response = $params;
    $this->update_attributes($params);
  }

  private function update_attributes($attributes = array()) {
    foreach($attributes as $attr_name => $attr_value) {
        if($attr_name == "outputs" && is_array($attr_value)) {
          $this->create_outputs($attr_value);
        } elseif (!function_exists($this->$attr_name)) {
          $this->$attr_name = $attr_value;
        }
    }
  }

  private function create_outputs($outputs = array()) {
    foreach($outputs as $output_attrs) {
      if(!empty($output_attrs->label)) {
        $this->outputs[$output_attrs->label] = new Services_Zencoder_Output($output_attrs);
      } else {
        $this->outputs[] = new Services_Zencoder_Output($output_attrs);
      }
    }
  }
}
