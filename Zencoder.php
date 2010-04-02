<?php
/*

  Zencoder API PHP Library
  Version: 0.1
  Author: Steve Heffernan <steve@zencoder.com>
  See the README file for info on how to use this library.

*/


// FlixCloud Transcoding Job
class ZencoderJob {

  var $api_key;     // Provided at https://flixcloud.com/settings
  var $recipe_id;   // Can be found at http://flixcloud.com/overviews/recipes
  var $api_url = "https://www.flixcloud.com/jobs";

  var $input;       // FlixCloudJobInputFile Object
  var $output;      // FlixCloudJobOutputFile Object
  var $watermark;   // FlixCloudJobWatermarkFile Object
  var $thumbnail;   // FlixCloudJobThumbnailFile Object

  var $notification_url; // Sets notification URL if supplied.
  var $pass_through; // Allows user to pass an internally used value back with notification.
  
  // cURL Options
  var $timeout = 0; // Time in seconds to timeout send request. 0 is no timeout.

  // Dealing with the certificate. Still a sketchy area.
  // If you learn anything from working with it let me know.
  var $insecure;    // Bypasses verifying the certificate if needed. Like curl -k or --insecure. Still somewhat secure.
  var $certificate; // Full path to the www.flixcloud.com.pem certificate. Not required most of the time. Look up CURLOPT_CAINFO for more info.
  var $certificate_dir; // Directory where certs live. Not required most of the time. Look up CURLOPT_CAPATH for more info.

  var $success;
  var $errors = array();

  var $status_code; // Used for testing
  var $result;      // Used for testing
  var $final_xml;   // Used for testing

  var $id;                  // Provided after job has been sent
  var $initialized_job_at;  // Provided after job has been sent

  // Initialize. API key is required. Option to load up job info on initialize using params hash.
  function FlixCloudJob($api_key, $params = "") {
    $this->api_key = $api_key;
    if (is_array($params)) {
      if($params["recipe_id"]) $this->recipe_id = $params["recipe_id"];

      if($params["input_url"]) $this->set_input($params["input_url"], array("user" => $params["input_user"], "password" => $params["input_password"]));
      if($params["output_url"]) $this->set_output($params["output_url"], array("user" => $params["output_user"], "password" => $params["output_password"]));
      if($params["watermark_url"]) $this->set_watermark($params["watermark_url"], array("user" => $params["watermark_user"], "password" => $params["watermark_password"]));
      if($params["thumbnail_url"]) $this->set_thumbnail($params["thumbnail_url"], array("prefix" => $params["thumbnail_prefix"], "user" => $params["thumbnail_user"], "password" => $params["thumbnail_password"]));
      
      if($params["notification_url"]) $this->notification_url = $params["notification_url"];
      if($params["pass_through"]) $this->pass_through = $params["pass_through"];

      if($params["insecure"]) $this->insecure = true;
      if($params["certificate"]) $this->certificate = $params["certificate"];

      if($params["api_url"]) $this->api_url = $params["api_url"];

      // If params array is used it sends by default
      if($params["send"] !== false) $this->send();
    } elseif(intval($params) > 0) {
      $this->recipe_id = $params; // Backwards compatible for when recipe was second arg.
    }
  }

  // Create input file object from URL and option user credentials
  function set_input($url, $params = array()) {
    $this->input = new FlixCloudJobInputFile($url, $params);
  }

  // Create output file object from URL and option user credentials
  function set_output($url, $params = array()) {
    $this->output = new FlixCloudJobOutputFile($url, $params);
  }

  // Create watermark file object from URL and option user credentials
  function set_watermark($url, $params = array()) {
    $this->watermark = new FlixCloudJobWatermarkFile($url, $params);
  }

  // Create thumbnail file object from URL and option user credentials
  function set_thumbnail($url, $params = array()) {
    $this->thumbnail = new FlixCloudJobThumbnailFile($url, $params);
  }

  // Check that all required info is available and valid. Used before sending.
  function validate() {
    if ( !function_exists("curl_version")) $this->errors[] = "cURL is not installed.";
    if ( !$this->api_key) $this->errors[] = "API key is required.";
    if ( !$this->recipe_id || intval($this->recipe_id) <= 0) $this->errors[] = "Recipe ID is required and must be an integer.";
    // Validate the different file types.
    foreach(array($this->input, $this->output, $this->watermark, $this->thumbnail) as $job_file) {
      if($job_file) {
        if ( !$job_file->validate()) $this->errors = array_merge($this->errors, $job_file->errors);
      }
    }

    // Return false if any errors.
    if(count($this->errors) > 0) return false;
    return true;
  }

  // Send job request to FlixCloud
  function send() {
    $this->success = false;
    if ( !$this->validate()) return false;

    $this->final_xml = $this->get_job_xml();

    // Set up cURL connection
    $ch = curl_init($this->api_url);
    curl_setopt_array($ch, array(
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_HEADER => 0, // Don't return the header in result
      CURLOPT_HTTPHEADER => array("Accept: text/xml", "Content-type: application/xml"), // Required headers
      CURLOPT_POST => 1,
      CURLOPT_POSTFIELDS => $this->final_xml, // XML data
      CURLOPT_CONNECTTIMEOUT => $this->timeout,
    ));

    if($this->certificate) {
      curl_setopt($ch, CURLOPT_CAINFO, $this->certificate);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    }

    if($this->certificate_dir) {
      curl_setopt($ch, CURLOPT_CAPATH, $this->certificate_dir);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    }

    if($this->insecure) {
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    }

    // Execute send
    $this->result = curl_exec($ch);

    if (curl_errno($ch)) {
      $this->errors[] = "Curl error (".curl_errno($ch)."): ".curl_error($ch);
      return false;
    }

    // Store the HTTP status code given (201, 400, etc.)
    $this->status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Close cURL connection;
    curl_close($ch);

    // Respond based on HTTP status code.
    switch ($this->status_code) {
      case "201": // Successful
        // Parse returned XML and get vars array
        $xml_obj = get_object_vars(new SimpleXMLElement($this->result));
        // Set job ID
        $this->id = $xml_obj["id"];
        // Set job's initialized time in UTC using the format YYYY-MM-DDTHH:MM:SSZ
        $this->initialized_job_at = $xml_obj["initialized-job-at"];
        $this->success = true;
        return true;

      case "200":
        $xml_obj = get_object_vars(new SimpleXMLElement($this->result));
        $this->errors[] = $xml_obj["error"];
        $this->array_flatten($this->errors);
        return false;

      case "302":
        $this->errors[] = "Redirection occurred (302). There's an error in the API URL.";
        return false;

      case "400":
        $xml_obj = get_object_vars(new SimpleXMLElement($this->result));
        $this->errors[] = $xml_obj["description"].$xml_obj["error"];
        return false;

      case "401":
        $this->errors[] = "Access denied (401). Probably a bad API key.";
        return false;

      case "404":
        $this->errors[] = "Page not found (404). The API url may be wrong, or the site is down.";
        return false;

      case "500":
        $this->errors[] = "The server returned an error (500). The API may be down.";
        return false;

      default:
        $temp_status_code = ($this->status_code) ? $this->status_code : "none";
        $temp_result = ($this->result) ? "\"".$this->result."\"" : "empty";
        $this->errors[] = "An unknown error has occurred."." Status Code: ".$temp_status_code.". Result ".$temp_result;
        return false;
    }
  }

  // Organize job data needed for API into a hash, then convert to XML.
  function get_job_xml() {

    // Watermark is optional. If left blank it won't be included in XML.
    if($this->watermark) {
      $watermark_hash = $this->watermark->get_data_hash();
    }

    // Thumbnail is optional. If left blank it won't be included in XML.
    if($this->thumbnail) {
      $thumbnail_hash = $this->thumbnail->get_data_hash();
    }

    // API XML in hash form
    // If key is an integer (0 included) value will be added to XML as is.
    $xml_hash = array(
      0 => '<?xml version="1.0" encoding="UTF-8"?>',
      "api-request"       => array(
        "api-key"           => $this->api_key,
        "recipe-id"         => $this->recipe_id,
        "file-locations"  => array(
          "input"           => $this->input->get_data_hash(),
          "output"          => $this->output->get_data_hash(),
          "watermark"       => $watermark_hash,
          "thumbnails"      => $thumbnail_hash,
        ),
      ),
    );
    
    if($this->notification_url) {
      $xml_hash["api-request"]["notification-url"] = $this->notification_url;
    }
    
    if($this->pass_through) {
      $xml_hash["api-request"]["pass-through"] = $this->pass_through;
    }
    
    return $this->hash_to_xml($xml_hash);
  }

  // Create XML from a hash
  // Does some uneccessary formatting of the XML, but it's nice for debugging.
  function hash_to_xml($hash, $starting_indent = 0) {
    $xml = "";
    $indent = "";
    for($i=0; $i<$starting_indent; $i++) { $indent .= "  "; }
    foreach($hash as $tag_name => $tag_contents) {
      $xml .= $indent;
      // If key is an integer (0 included) value will be added to XML as is.
      if (is_int($tag_name)) {
        $xml .= $tag_contents."\n";
      } elseif (is_array($tag_contents)) {
        $xml .= $this->tag($tag_name, "\n".$this->hash_to_xml($tag_contents, $starting_indent+1).$indent)."\n";
      } elseif ($tag_contents) {
        $xml .= $this->tag($tag_name, htmlspecialchars($tag_contents))."\n";
      }
    }

    return $xml;
  }

  // Create a simple XML tag from a name and content. Escape special characters (&<>).
  function tag($tag_name, $tag_content) {
    return '<'.$tag_name.'>'.$tag_content.'</'.$tag_name.'>';
  }

  // Flatten a basic array. Kinda surpised PHP doesn't have this.
  function array_flatten(&$array) {
    foreach($array as $key => $val) {
      if(is_array($val)) {
        unset($array[$key]);
        $this->array_flatten($val);
        $array = array_merge($array, $val);
      }
    }
    return $array;
  }

}

// Class for holding media file info (file url, user, pass)
class FlixCloudJobFile {

  var $name = "base"; // Used in errors
  var $url;      // Location of file including tranfer protocol (http, ftp, etc.)
  var $user;          // Username
  var $password;      // Password
  var $protocol;      // Set and used in validating.
  var $errors = array();

  // Info received from notification
  var $width;     // In pixels
  var $height;    // In pixels
  var $size;      // In bytes
  var $duration;  // In milliseconds
  var $cost;      // In millicents

  // Initialize
  function FlixCloudJobFile($url, $params = array()) {
    $this->url = trim($url);
    $this->set_attributes($params);
  }

  // Validate that all needed data is available.
  function validate() {
    if( !$this->url) $this->errors[] = $this->name." file url required.";

    // Return false if any errors.
    if(count($this->errors) > 0) return false;
    return true;
  }

  // Check if protocal supplied matches an accepted protocol for the file type
  function check_protocol() {
    preg_match ("/^[^:]+/i", $this->url, $matches);
    $this->protocol = $matches[0];
    if( !in_array($this->protocol, $this->acceptable_protocols)) {
      return false;
    }
    return true;
  }

  // Return a hash of values to be turned into XML later
  function get_data_hash() {
    $hash = array("url" => $this->url);
    if($this->user) {
      $hash["parameters"] = array(
        "user" => $this->user,
        "password" => $this->password
      );
    }
    return $hash;
  }

  function set_attributes($hash) {
    if(is_array($hash)) {
      foreach($hash as $key => $value) {
        if(strpos($key, "-")) $key = str_replace("-", "_", $key);
        $this->$key = $value;
      }
    }
  }
}

// Input File Data Object
class FlixCloudJobInputFile extends FlixCloudJobFile {
  var $name = "Input";
  //var $acceptable_protocols = array("http", "https", "ftp", "sftp", "s3");
}

// Output File Data Object
class FlixCloudJobOutputFile extends FlixCloudJobFile {
  var $name = "Output";
  //var $acceptable_protocols = array("ftp", "sftp", "s3");
}

// Watermark File Data Object
class FlixCloudJobWatermarkFile extends FlixCloudJobFile {
  var $name = "Watermark";
  //var $acceptable_protocols = array("http", "https", "ftp", "sftp", "s3");
}

// Thumbnail File Data Object
class FlixCloudJobThumbnailFile extends FlixCloudJobFile {
  var $name = "Thumbnail";
  var $prefix;

  // Info received from notification
  var $total_size;
  var $number_of_thumbnails;

  function get_data_hash() {
    $hash = array("url" => $this->url);
    $hash["prefix"] = $this->prefix;

    if($this->user) {
      $hash["parameters"] = array(
        "user" => $this->user,
        "password" => $this->password
      );
    }
    return $hash;
  }
}

class FlixCloudNotificationHandler {
  function catch_and_parse() {
    $incoming = file_get_contents('php://input');
	$incoming = trim($incoming);
    $hash = get_object_vars(new SimpleXMLElement($incoming));
    return new FlixCloudJobNotification($hash);
  }
}

// Class for catching and parsing XML data sent from FlixCloud when job is finished.
// Notification URL must be set in https://flixcloud.com/settings
class FlixCloudJobNotification {

  var $original_hash;          // From XML object

  var $id;                     // The job's ID
  var $initialized_job_at;     // When the job was initialized. UTC YYYY-MM-DDTHH:MM:SSZ
  var $recipe_name;            // Name of recipe used
  var $recipe_id;              // ID of recipe
  var $state;                  // failed_job, cancelled_job, or successful_job
  var $error_message;          // Error message if there was one.
  var $finished_job_at;        // When the job finished. UTC YYYY-MM-DDTHH:MM:SSZ
  var $pass_through;           // User defined value passed from job request

  // FlixCloudJobFile Objects
  var $input;                 // $fcjn->input->url      (url, width, height, size, duration, cost)
  var $output;                // $fcjn->output->url,    (url, width, height, size, duration, cost)
  var $watermark;             // $fcjn->watermark->url  (url, size, cost)
  var $thumbnail;             // $fcjn->thumbnail->url  (url, total_size, number_of_thumbnails, cost)

  function FlixCloudJobNotification($hash) {

    $this->original_hash = $hash;

    // Set attributes from XML on object
    // Creat job file objects from file info
    foreach($hash as $hash_key => $hash_value) {
      $method_name = "set_".str_replace("-", "_", $hash_key);
      if(method_exists($this, $method_name)) {
        $this->$method_name($hash_value);
      } else {
        $var_name = str_replace("-", "_", $hash_key);
        $this->$var_name = trim($hash_value);
      }
    }
  }

  function set_input_media_file($hash_value) {
    $this->input = new FlixCloudJobInputFile($hash_value["url"], get_object_vars($hash_value));
  }

  function set_output_media_file($hash_value) {
    $this->output = new FlixCloudJobOutputFile($hash_value["url"], get_object_vars($hash_value));
  }

  function set_watermark_file($hash_value) {
    $this->watermark = new FlixCloudJobWatermarkFile($hash_value["url"], get_object_vars($hash_value));
  }

  function set_thumbnail_media_file($hash_value) {
    $this->thumbnail = new FlixCloudJobThumbnailFile($hash_value["url"], get_object_vars($hash_value));
  }

}


// Class for checking the status of a job and its tasks.
class FlixCloudJobStatusChecker {

  var $api_key;
  var $job_id;

  var $result;
  var $result_hash;
  var $errors = array();
  var $check_successful;

  function FlixCloudJobStatusChecker($api_key, $job_id) {
    $this->api_key = $api_key;
    $this->job_id = $job_id;
    $this->check_successful = $this->send();
  }

  function send() {

    // Set up cURL connection
    $ch = curl_init("https://www.flixcloud.com/jobs/".$this->job_id."/status");
    curl_setopt_array($ch, array(
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_HEADER => 0, // Don't return the header in result
      CURLOPT_HTTPHEADER => array("Accept: text/xml", "Content-type: application/xml"), // Required headers
      CURLOPT_POST => 0,
      CURLOPT_CONNECTTIMEOUT => 0
    ));

    // Bypass cert check for status check to avoid issues
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

    // HTTP Basic Auth
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, 'user:'.$this->api_key);

    // Execute
    $this->result = curl_exec($ch);

    // Close cURL connection;
    curl_close($ch);

    if($this->result) {
      if(strpos($this->result, "Access denied.") > -1) {
        $this->errors[] = $this->result;
        return false;
      } else {
        $this->result_hash = get_object_vars(new SimpleXMLElement($this->result));
        return true;
      }
    } else {
      if (curl_errno($ch)) {
        $this->errors[] = "Curl error (".curl_errno($ch)."): ".curl_error($ch);
      }
      return false;
    }
  }

  function get_job_status() {
    if($this->check_successful) {
      return $this->result_hash["task-state"];
    } else {
      return false;
    }
  }

}

// Function that makes checking the overall status of a job a little simpler.
function get_flix_cloud_job_status($api_id, $job_id) {
  $checker = new FlixCloudJobStatusChecker($api_id, $job_id);
  $status = $checker->get_job_status();
  return ($status) ? $status : "Status Unavailable";
}

?>
