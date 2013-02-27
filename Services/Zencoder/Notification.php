<?php
/**
 * Zencoder API client interface.
 *
 * @category Services
 * @package  Services_Zencoder
 * @author   Michael Christopher <m@zencoder.com>
 * @version  Release: 2.1.2
 * @license  http://creativecommons.org/licenses/MIT/MIT
 * @link     http://github.com/zencoder/zencoder-php
 */

class Services_Zencoder_Notification extends Services_Zencoder_Object
{
    /**
    * The job that the notification references
    * 
    * @var Services_Zencoder_Job
    */
    public $job;

    public function __construct($params)
    {
        if(empty($params->job)) $params->job = new stdClass();
        if(!empty($params->input)) $params->job->input_media_file = $params->input;
        if(!empty($params->outputs) && is_array($params->outputs)) {
            foreach ($params->outputs as $output) $params->job->outputs[] = $output;
        } else {
            if(!empty($params->output)) $params->job->outputs[] = $params->output;
        }
        $this->job = new Services_Zencoder_Job($params->job);
    }
}
