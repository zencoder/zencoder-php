<?php
/*

  Zencoder API PHP Library
  Version: 1.2
  See the README file for info on how to use this library.

*/
define('ZENCODER_LIBRARY_NAME',  "ZencoderPHP");
define('ZENCODER_LIBRARY_VERSION',  "1.2");

// Add JSON functions for PHP < 5.2.0
if(!function_exists('json_encode')) {
  require_once(dirname(__FILE__) . '/lib/JSON.php');
  $GLOBALS['JSON_OBJECT'] = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
  function json_encode($value) { return $GLOBALS['JSON_OBJECT']->encode($value); }
  function json_decode($value) { return $GLOBALS['JSON_OBJECT']->decode($value); }
}

// Check that cURL extension is enabled
if(!function_exists('curl_init')) {
  throw new Exception('You must have the cURL extension enabled to use this library.');
}

class ZencoderJob {

  var $new_job_url = "https://app.zencoder.com/api/v1/jobs";
  var $new_job_params = array();
  var $created = false;
  var $errors = array();

  // Attributes
  var $id;
  var $test;
  var $state;

  var $outputs = array();

  // Initialize
  function ZencoderJob($params, $options = array()) {

    // Build using params if not sending request
    if(!empty($options["build"])) {
      $this->update_attributes($params);
      return true;
    }
    
    if(!empty($options["url"])) $this->new_job_url = $options["url"];
    $this->new_job_params = $params;
    $this->created = $this->create();
  }

  // Send Job Request to API
  function create() {
    // Send request
    $request = new ZencoderRequest($this->new_job_url, false, $this->new_job_params);

    if($request->successful) {
      $this->update_attributes($request->results);
      return true;
    } else {
      $this->errors = array_merge($this->errors, $request->errors);
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
      if(!empty($output_attrs["label"])) {
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

// General API request class
class ZencoderRequest {

  var $successful = false;
  var $errors = array();
  var $raw_results;
  var $results;

  function ZencoderRequest($url, $api_key = "", $params = "", $options = "") {

    // Add api_key to url if supplied
    if($api_key) {
      $url .= "?api_key=".$api_key;
    }

    // Get JSON
    if(is_string($params)) {
      $json = trim($params);
    } else if(is_array($params)) {
      $json = json_encode($params);
    } else {
      $json = false;
    }

    // Create request
    $request = new ZencoderCURL($url, $json);

    // Check for connection errors
    if ($request->connected == false) {
      $this->errors[] = $request->error;
      return;
    }

    $status_code = intval($request->status_code);
    $this->raw_results = $request->results;

    // Parse returned JSON
    $this->results = json_decode($this->raw_results, true);

    // Return based on HTTP status code
    if($status_code >= 200 && $status_code <= 206) {
      $this->successful = true;
    } else {
      // Get job request errors if any
      if(is_array($this->results["errors"])) {
        foreach($this->results["errors"] as $error) {
          $this->errors[] = $error;
        }
      } else {
        $this->errors[] = "Unknown Error\n\nHTTP Status Code: ".$request->status_code."\n"."Raw Results: \n".$this->raw_results;
      }
    }
  }
}

// ZencoderCURL
// The connection class to perform the actual request to the surver
// using cURL http://php.net/manual/en/book.curl.php
class ZencoderCURL {

  var $options = array(
    CURLOPT_RETURNTRANSFER => 1, // Return content of the url
    CURLOPT_HEADER => 0, // Don't return the header in result
    CURLOPT_HTTPHEADER => array("Content-Type: application/json", "Accept: application/json"),
    CURLOPT_CONNECTTIMEOUT => 0, // Time in seconds to timeout send request. 0 is no timeout.
    CURLOPT_FOLLOWLOCATION => 1, // Follow redirects.
    CURLOPT_SSL_VERIFYPEER => 1,
    CURLOPT_SSL_VERIFYHOST => 1
  );

  var $connected;
  var $results;
  var $status_code;
  var $error;

  // Initialize
  function ZencoderCURL($url, $json, $options = array()) {

    // If PHP in safe mode, disable following location
    if( ini_get('safe_mode') ) {
      $this->options[CURLOPT_FOLLOWLOCATION] = 0;
    }

    // Add library details to request
    $this->options[CURLOPT_HTTPHEADER][] = "Zencoder-Library-Name: ".ZENCODER_LIBRARY_NAME;
    $this->options[CURLOPT_HTTPHEADER][] = "Zencoder-Library-Version: ".ZENCODER_LIBRARY_VERSION;

    // If posting data
    if($json) {
      $this->options[CURLOPT_POST] = 1;
      $this->options[CURLOPT_POSTFIELDS] = $json;
    }

    // Add cURL options to defaults (can't use array_merge)
    foreach($options as $option_key => $option) {
      $this->options[$option_key] = $option;
    }

    // Initialize session
    $ch = curl_init($url);

    // Set transfer options
    curl_setopt_array($ch, $this->options);

    // Execute session and store returned results
    $this->results = curl_exec($ch);
    
    // Code based on Facebook PHP SDK
    // Retries request if unable to validate cert chain
    if (curl_errno($ch) == 60) { // CURLE_SSL_CACERT
      curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/lib/zen_ca_chain.crt');
      $this->results = curl_exec($ch);
    }

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

// Capture incoming notifications from Zencoder to your app
class ZencoderOutputNotification {

  var $output;
  var $job;

  function ZencoderOutputNotification($params) {
    if(!empty($params["output"])) $this->output = new ZencoderOutputFile($params["output"]);
    if(!empty($params["job"])) $this->job = new ZencoderJob($params["job"], array("build" => true));
  }

  function catch_and_parse() {
    $notificiation_data = json_decode(trim(file_get_contents('php://input')), true);
    return new ZencoderOutputNotification($notificiation_data);
  }
}
