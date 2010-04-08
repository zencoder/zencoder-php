<?php
/*

  Zencoder API PHP Library
  Version: 1.0
  See the README file for info on how to use this library.

*/
define('ZENCODER_LIBRARY_NAME',  "ZencoderPHP");
define('ZENCODER_LIBRARY_VERSION',  "1.0");

// Add JSON functions for PHP < 5.2.0
if(!function_exists('json_encode')) {
  require_once('lib/JSON.php');
  $GLOBALS['JSON_OBJECT'] = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
  function json_encode($value) { return $GLOBALS['JSON_OBJECT']->encode($value); }
  function json_decode($value) { return $GLOBALS['JSON_OBJECT']->decode($value); }
}

class ZencoderJob {

  var $create_url = "https://app.zencoder.com/api/jobs";
  var $create_params = array();
  var $create_json;
  var $created = false;
  var $errors = array();

  // Attributes
  var $id;
  var $outputs = array();

  // Initialize
  function ZencoderJob($params, $options = array()) {

    // Build using params if not sending request
    if($options["build"]) {
      $this->update_attributes($params);
      return true;
    }

    // Get JSON
    if(is_string($params)) {
      $this->create_json = trim($params);
      $this->create_params = json_decode($params, true);
    } else if(is_array($params)) {
      $this->create_json = json_encode($params);
      $this->create_params = $params;
    }

    $this->created = $this->create();
  }

  // Send Job Request to API
  function create() {

    // Send request
    $connection = new ZencoderCURL($this->create_url, $this->create_json);

    // Check for connection errors
    if ($connection->connected == false) {
      $this->errors[] = $connection->error;
      return false;
    }

    // Parse returned JSON
    $parsed_results = json_decode($connection->results, true);

    // Return based on HTTP status code
    if($connection->status_code == "201") {
      $this->update_attributes($parsed_results);
      return true;
    } else {
      // Get job request errors if any
      if(is_array($parsed_results["errors"])) {
        foreach($parsed_results["errors"] as $error) {
          $this->errors[] = $error;
        }
      } else {
        $this->errors[] = "Unknown Error\n\nHTTP Status Code: ".$connection->status_code."\n";"Raw Results: \n".$connection->results;
      }
      return false;
    }
  }

  // Add/Update attributes on the job object.
  function update_attributes($attributes = array()) {
    foreach($attributes as $attr_name => $attr_value) {
      // Create output file objects
      if($attr_name == "outputs" && is_array($attr_value)) {
        $this->create_outputs($attr_value);
      } elseif (!function_exists($this->$attr_name)) {
        $this->$attr_name = $attr_value;
      }
    }
  }

  // Create output file objects from returned parameters.
  // Use the Label for the key if avaiable.
  function create_outputs($outputs = array()) {
    foreach($outputs as $output_attrs) {
      if($output_attrs["label"]) {
        $this->outputs[$output_attrs["label"]] = new ZencoderOutputFile($output_attrs);
      } else {
        $this->outputs[] = new ZencoderOutputFile($output_attrs);
      }
    }
  }
}


class ZencoderOutputFile {

  var $id;
  var $label;
  var $url;
  var $state;
  var $error_message;
  var $error_link;

  function ZencoderOutputFile($attributes = array()) {
    $this->update_attributes($attributes);
  }

  // Add/Update attributes on the file object.
  function update_attributes($attributes = array()) {
    foreach($attributes as $attr_name => $attr_value) {
      if(!function_exists($this->$attr_name)) {
        $this->$attr_name = $attr_value;
      }
    }
  }
}


class ZencoderOutputNotification {

  var $output;
  var $job;

  function ZencoderOutputNotification($params) {
    if($params["output"]) $this->output = new ZencoderOutputFile($params["output"]);
    if($params["job"]) $this->job = new ZencoderJob($params["job"], array("build" => true));
  }

  function catch_and_parse() {
    $notificiation_data = json_decode(trim(file_get_contents('php://input')), true);
    return new ZencoderOutputNotification($notificiation_data);
  }
}


// Connection class
class ZencoderCURL {

  var $url;
  var $options = array(
    CURLOPT_RETURNTRANSFER => 1, // Return content of the url
    CURLOPT_HEADER => 0, // Don't return the header in result
    CURLOPT_HTTPHEADER => array("Content-Type: application/json", "Accept: application/json"),
    CURLOPT_CONNECTTIMEOUT => 0, // Time in seconds to timeout send request. 0 is no timeout.
    CURLOPT_FOLLOWLOCATION => 1, // Follow redirects.
  );

  var $connected;
  var $results;
  var $status_code;
  var $error;

  // Initialize
  function ZencoderCURL($url, $data, $options = array()) {
    $this->url = $url;

    // Add library details to request
    $this->options[CURLOPT_HTTPHEADER][] = "Zencoder-Library-Name: ".ZENCODER_LIBRARY_NAME;
    $this->options[CURLOPT_HTTPHEADER][] = "Zencoder-Library-Version: ".ZENCODER_LIBRARY_VERSION;

    // If posting data
    if($data) {
      $this->options[CURLOPT_POST] = 1;
      $this->options[CURLOPT_POSTFIELDS] = $data;
    }

    // Add cURL options to defaults (can't use array_merge)
    foreach($options as $option_key => $option) {
      $this->options[$option_key] = $option;
    }

    // Initialize session
    $ch = curl_init($this->url);

    // Set transfer options
    curl_setopt_array($ch, $this->options);

    // Execute session and store returned results
    $this->results = curl_exec($ch);

    // Store the HTTP status code given (201, 404, etc.)
    $this->status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Check for cURL error
    if (curl_errno($ch)) {
      $this->error = 'cURL connection error ('.curl_errno($ch).'): '.htmlspecialchars(curl_error($ch)).' <a href="http://www.google.com/search?q='.urlencode("curl error ".curl_error($ch)).'">Search</a>';
      $this->connected = false;
    } else {
      $this->connected = true;
    }

    // Close session
    curl_close($ch);
  }
}
