<?php
/**
 * Zencoder API client interface.
 *
 * @category Services
 * @package  Services_Zencoder
 * @author   Michael Christopher <m@zencoder.com>
 * @version  Release: 2.0.2
 * @license  http://creativecommons.org/licenses/MIT/MIT
 * @link     http://github.com/zencoder/zencoder-php
 */

class Services_Zencoder_Notification extends Services_Zencoder_Object
{
    /**
    * The output that the notification references
    * 
    * @var Services_Zencoder_Output
    */
    public $output;
    /**
    * The job that the notification references
    * 
    * @var Services_Zencoder_Job
    */
    public $job;

    public function __construct($params)
    {
        if(!empty($params["output"])) $this->output = new Services_Zencoder_Output($params["output"]);
        if(!empty($params["job"])) $this->job = new Services_Zencoder_Job($params["job"]);
    }
}
