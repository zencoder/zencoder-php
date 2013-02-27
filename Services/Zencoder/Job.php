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

class Services_Zencoder_Job extends Services_Zencoder_Object
{
    /**
    * Array of the outputs on the job
    * 
    * @var array
    */
    public $outputs = array();
    /**
    * Array of the thumbnails on the job
    * 
    * @var array
    */
    public $thumbnails = array();
    /**
    * Services_Zencoder_Input object containing information on the input file
    * 
    * @var Services_Zencoder_Input
    */
    public $input;
    
    /**
     * Services_Zencoder_Stream object containing information on the stream for
     * live transcoding
     * 
     * @var Services_Zencoder_Stream
     */
    public $stream;
    
    /**
    * A copy of the raw API response for debug purposes
    * 
    * @var mixed
    */
    protected $raw_response;

    /**
    * Create a new Services_Zencoder_Job object.
    *
    * @param mixed               $params      API response
    */
    public function __construct($params)
    {
        $this->raw_response = $params;
        $this->_update_attributes($params);
    }

    private function _update_attributes($attributes = array())
    {
        foreach($attributes as $attr_name => $attr_value) {
            if (($attr_name == "output_media_files" || $attr_name == "outputs") && is_array($attr_value)) {
                $this->_create_outputs($attr_value);
            } elseif ($attr_name == "thumbnails" && is_array($attr_value)) {
                $this->_create_thumbnails($attr_value);
            } elseif ($attr_name == "input_media_file" && is_object($attr_value)) {
                $this->input = new Services_Zencoder_Input($attr_value);
            } elseif ($attr_name == "stream" && is_object($attr_value)){
                $this->stream = new Services_Zencoder_Stream($attr_value);
            } elseif (is_array($attr_value) || is_object($attr_value)) {
                $this->_update_attributes($attr_value);
            } elseif (empty($this->$attr_name)) {
                $this->$attr_name = $attr_value;
            }
        }
    }

    private function _create_thumbnails($thumbnails = array())
    {
        foreach($thumbnails as $thumb_attrs) {
            if(!empty($thumb_attrs->group_label)) {
                $this->thumbnails[$thumb_attrs->group_label] = new Services_Zencoder_Thumbnail($thumb_attrs);
            } else {
                $this->thumbnails[] = new Services_Zencoder_Thumbnail($thumb_attrs);
            }
        }
    }
}
