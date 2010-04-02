Zencoder API PHP Library
==========================

Author:  [Steve Heffernan](http://www.steveheffernan.com) (steve (a) sevenwire () c&#1;om)  
Company: [Sevenwire - Web Application Development](http://sevenwire.com)  
Version: 1.1  
Date:    08-31-2009  
Repository: <http://github.com/flixcloud/flix_cloud-php/>

For more details on the FlixCloud API requirements visit  
<http://flixcloud.com/api>

There are three main functions: **Job Requests**, **Notification Handling**, and **Checking Job Status**


TRANSCODING JOB REQUEST
------------------------
The FlixCloudJob object uses cURL to send an XML formatted job request to
https://www.flixcloud.com/jobs. It then uses the SimpleXMLElement library
to parse any returned XML.

 **API key is required and can be found at <https://flixcloud.com/settings>**  
 **Recipe ID is required and can be found at <http://flixcloud.com/overviews/recipes>**


### EXAMPLE 1 (using params hash)

    require_once("FlixCloud.php");

    $fcj = new FlixCloudJob("2j1l:kd3add:0:ivzf:2e1y", array(
      "recipe_id"           => 99,

      "input_url"           => "http://www.example.com/videos/input.mpg",
      // "input_user"          => "username", // Optional
      // "input_password"      => "password", // Optional

      "output_url"          => "sftp://www.example.com/httpdocs/videos/output.flv",
      "output_user"         => "username",
      "output_password"     => "password",

      // If your recipe uses a watermark
      "watermark_url"       => "http://www.example.com/videos/watermark.png",
      // "watermark_user"      => "username", // Optional
      // "watermark_password"  => "password", // Optional

      // If your recipe uses thumbnails
      "thumbnail_url"       => "sftp://www.example.com/httpdocs/videos/thumbs/",
      "thumbnail_prefix"    => "thumb_",
      "thumbnail_user"      => "username",
      "thumbnail_password"  => "password",

      // "notification_url" => "http://www.example.com/notification.php", // Optional. Can be set in account settings.
      // "pass_through" => "Blah blah", // Optional. Allows you to pass back a specific value with the notification.
      // "insecure"         => true,  // Use if certificate won't verify
      // "send"             => false, // Use if you don't want to send job immeditely.

    ));

    if($fcj->success) {
      // Job was successfully requested. Store job ID and start time if needed.
      echo "Success. Job ID is ".$fcj->id." Submitted at ".$fcj->initialized_job_at;
    } else {
      // Do something with the errors
      foreach($fcj->errors as $error) {
        echo $error;
      }
    }


### EXAMPLE 2 (setting properties separately)

    require_once("FlixCloud.php");

    // First parameter is API key, second is Recipe ID
    $fcj = new FlixCloudJob("2j1l:kd3add:0:ivzf:2e1y");
    $fcj->recipe_id = 99;
    $fcj->set_input("http://www.example.com/videos/input.mpg"); // Add user/password array if needed
    $fcj->set_output("sftp://www.example.com/httpdocs/videos/output.flv", array("user" => "username", "password" => "password"));
    $fcj->set_watermark("http://www.example.com/videos/watermark.png"); // Add user/password array if needed
    $fcj->set_thumbnail("sftp://www.example.com/httpdocs/videos/thumbs/", array("prefix" => "thumb_", "user" => "username", "password" => "password"));

    if($fcj->send()) {
      // Job was successfully requested. Store job ID if needed.
      echo "Success. Job ID is ".$fcj->id." Submitted at ".$fcj->initialized_job_at;
    } else {
      // Do something with the errors
      foreach($fcj->errors as $error) {
        echo $error;
      }
    }



NOTIFICATION HANDLING
----------------------
The FlixCloudNotificationHandler class is used to capture and parse XML data sent from
FlixCloud when a job has been completed.

The possible states of the FlixCloudJobNotification object are "successful\_job",
"cancelled\_job", or "failed\_job". A callback function allows you to respond accordingly.

**Notification URL must be set in <https://flixcloud.com/settings>**  
**A file following the example below should be at that URL**

### EXAMPLE

    require_once("FlixCloud.php");

    $job_notification = FlixCloudNotificationHandler::catch_and_parse();

    switch ($job_notification->state) {
        case "successful_job":

          echo $job_notification->id."\n";

          echo $job_notification->output->url."\n";
          echo $job_notification->output->cost."\n";

          echo $job_notification->input->url."\n";
          echo $job_notification->input->cost."\n";

          // echo $job_notification->watermark->url."\n";
          // echo $job_notification->thumbnail->url."\n";
          // echo $job_notification->pass_through."\n";

          // Other info available. See FlixCloudJobNotification code.

          break;
        case "cancelled_job":
          // Do something else
          break;
        case "failed_job":
          echo $job_notification->error_message;
          break;
        default:
    }



CHECKING JOB STATUS
-------------------
The easiest way to check a job's overall status is to use the following function. The first argument is the API key. The second argument is the job ID that you want to check.

    require_once("FlixCloud.php");
    $job_status = get_flix_cloud_job_status("2j1l:kd3add:0:ivzf:2e1y", "12345");

There is actually more status info you can pull from the API, like progress for certain tasks. My guess is most people will just need the overall status of the job (i.e Transcoding, Uploading Output Files, Failed Job, etc.), so I haven't built in anything to specifically handle the individual tasks yet, however all of that info is available in the hash created from the XML in the FlixCloudJobStatusChecker object used in the following example. Check the API docs to see the specific info available.

    require_once("FlixCloud.php");
    $status_checker = new FlixCloudJobStatusChecker("2j1l:kd3add:0:ivzf:2e1y", "12345");
    $job_status = $status_checker->get_job_status();
    
    // Example of extra info
    $transcoding_task_state = $status_checker->$result_hash["transcoding-task"]["state"][0];
    $transcoding_task_progress = $status_checker->$result_hash["transcoding-task"]["state"][1];
    
    $input_file_upload_task_state = $status_checker->$result_hash["input-media-file-task"]["state"][0];
    $input_file_upload_task_progress = $status_checker->$result_hash["input-media-file-task"]["state"][1];



VERSIONS
---------

    Version 1.1.0 - 08/31/09    Added Status Checking (Class and basic function).
                                Added Notification URL setting.
                                Added Pass Through.
                                Changed require() to require_once() in readme examples. Not a required update.

    Version 1.0.8 - 07/08/09    Added thumbnail support for Job. (With help from Aaron Boushley)
                                Added thumnail info to Notification.
                                Removed User/Password validation on files. It blocked blank values, which could be valid.
                                Added handling of multi-level errors from XML response (flattened array)
                                Removed backward compatibility in Notification for $fcjn->input_media_file syntax, now must use $fcjn->input->url;

    Version 1.0.7 - 04/17/09    Updated notification object to match new XML version

    Version 1.0.6 - 04/14/09    Cleaned up the documentation a little.

    Version 1.0.5 - 04/06/09    Added options for pointing to the certificate or certificate directory.

    Version 1.0.4 - 04/06/09    Added option to connect insecurely incase SSL cert isn't working. (Like curl -k)

    Version 1.0.3 - 04/06/09    Added backward compatability for recipe as second argument
                                Added Curl error checking
                                Added more detailed unknown error reporting
                                Formatted XML a little

    Version 1.0.2 - 04/05/09    * Now requiring API key on object creation, and added an optional params hash
                                  for setting file info and sending on creation (example 2). *
                                * Recipe is no longer second argument *
                                * Broke up notification handling into 2 objects (see example) *
                                    FlixCloudNotificationHandler => Catch and parse XML
                                    FlixCloudJobNotification => Holds job notification info.
                                Added escaping of special characters when creating XML tags.
                                Removed protocol checking for now, because may want to just let the API do that.
                                On job->send changed status code handling to a switch statement.

    Version 1.0.1 - 04/02/09    Fixed acceptable protocols for some of the file types.



NOTES:
-------
Suggestions -> (steve (a) sevenwire () c&#1;om)

### Windows
If you're using the curl command line tool on Windows, curl will search
for a CA cert file named "curl-ca-bundle.crt" in these directories and in
this order:
  1. application's directory
  2. current working directory
  3. Windows System directory (e.g. C:\windows\system32)
  4. Windows Directory (e.g. C:\windows)
  5. all directories along %PATH%
[link](http://curl.haxx.se/docs/sslcerts.html)