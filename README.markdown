Zencoder API PHP Library
==========================

Author:  [Steve Heffernan](http://www.steveheffernan.com) (steve (a) zencoder (.) c&#1;om)  
Company: [Zencoder - Online Video Encoder](http://zencoder.com)  
Version: 1.1  
Date:    2010-06-04  
Repository: <http://github.com/zencoder/zencoder-php/>  

For more details on the Zencoder API requirements visit  
<http://zencoder.com/docs/api>


ENCODING JOB
------------
The ZencoderJob object creates an encoding job using [cURL](http://zencoder.com/docs/glossary/curl/)
to send [JSON](http://zencoder.com/docs/glossary/json/) formatted parameters to Zencoder's encoding API.

### Step 1
Visit the [API builder](https://app.zencoder.com/api_builder) in your account,
and execute a successful encoding job.

### Step 2
Copy the successful JSON string, starting with the first curly brace "{",
and pass it as the parameters for a new ZencoderJob object. Execute the script on your server to test that it works.

#### Example
    <pre>
    <?php

    // Make sure this points to a copy of Zencoder.php on the same server as this script.
    require_once("zencoder-php/Zencoder.php");

    // New Encoding Job
    $encoding_job = new ZencoderJob('
      {
        "api_key": "93h630j1dsyshjef620qlkavnmzui3",
        "input": "s3://bucket-name/file-name.avi"
        "outputs": [
          {
            "label": "web"
          }
        ]
      }
    ');

    // Check if it worked
    if ($encoding_job->created) {
      // Success
      echo "w00t! \n\n";
      echo "Job ID: ".$encoding_job->id."\n";
      echo "Output '".$encoding_job->outputs["web"]->label."' ID: ".$encoding_job->outputs["web"]->id."\n";
      // Store Job/Output IDs to update their status when notified or to check their progress.
    } else {
      // Failed
      echo "Fail :(\n\n";
      echo "Errors:\n";
      foreach($encoding_job->errors as $error) {
        echo $error."\n";
      }
    }

    echo "\nAll Job Attributes:\n";
    var_dump($encoding_job);

    ?>
    </pre>

### Step 3
Modify the above script to meet your needs.  
Your [API Request History](https://app.zencoder.com/api_requests) may come in handy.  
You can revisit your [API builder](https://app.zencoder.com/api_builder) to add/update parameters of the JSON.  

You can translate the JSON string into nested associative arrays so that you can dynamically change attributes like "input".  
The previous JSON example would become:

    $encoding_job = new ZencoderJob(array(
      "api_key" => "93h630j1dsyshjef620qlkavnmzui3",
      "input" => "s3://bucket-name/file-name.avi",
      "outputs" => array(
        array(
          "label" => "web"
        )
      )
    ));


GENERAL API REQUESTS
--------------------
A general API request can be used for all API functionality including **Job Listing**, **Job Details**, **Account Creation**, **Account Details** (even Job Creation if desired). See the [API docs](http://zencoder.com/docs/api/) for all possible API requests.
The first argument is the **API URL**.  
The second argument is your **API Key**.  
The third argument is the **request parameters** if needed. It can either be a JSON string or an array of parameters.


#### Example Job List Request

    $request = new ZencoderRequest(
      'https://app.zencoder/api/jobs',
      '93h630j1dsyshjef620qlkavnmzui3'
    );

    if ($request->successful) {
      print_r($request->results);
    } else {
      foreach($request->errors as $error) {
        echo $error."\n";
      }
    }

#### Example Account Creation Request

    $request = new ZencoderRequest(
      'https://app.zencoder/api/account', 
      false, // API key isn't needed for new account creation
      array(
        "terms_of_service" => "1",
        "email" => "test@example.com",
        "password" => "1234"
      )
    );

    if ($request->successful) {
      print_r($request->results);
    } else {
      foreach($request->errors as $error) {
        echo $error."\n";
      }
    }


NOTIFICATION HANDLING
----------------------
The ZencoderOutputNotification class is used to capture and parse JSON data sent from
Zencoder to your app when an output file has been completed.



### Step 1
Create a script to receive notifications, and upload it to a location on your server that is publicly accessible.

#### Example
    <?php

    // Make sure this points to a copy of Zencoder.php on the same server as this script.
    require("Zencoder.php");

    // Catch notification
    $notification = ZencoderOutputNotification::catch_and_parse();

    // Check output/job state
    if($notification->output->state == "finished") {
      echo "w00t!\n";

      // If you're encoding to multiple outputs and only care when all of the outputs are finished
      // you can check if the entire job is finished.
      if($notification->job->state == "finished") {
        echo "Dubble w00t!";
      }
    } elseif ($notification->output->state == "cancelled") {
      echo "Cancelled!\n";
    } else {
      echo "Fail!\n";
      echo $notification->output->error_message."\n";
      echo $notification->output->error_link;
    }

    ?>

### Step 2
In the parameters for an encoding job, add the URL for your script to the notifications array of each output you want to be notified for. 
Then submit the job to test if it works.  

**You can view the results at:**  
<https://app.zencoder.com/notifications>

#### Example
    ...
    "outputs" => array(
      array(
        "label" => "web",
        "notifications" => array("http://example.com.com/encoding/notification.php")
      ),
      array(
        "label" => "iPhone",
        "notifications" => array("http://example.com.com/encoding/notification.php")
      )
    )
    ...


### Step 3
Modify the above script to meet your needs.  
Your [notifications page](https://app.zencoder.com/notifications) will come in handy.

VERSIONS
---------
    Version 1.1 - 2010-06-04    Added General API Requests
    Version 1.0 - 2010-04-02    Jobs and Notifications.