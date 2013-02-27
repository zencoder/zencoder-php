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

class Services_Zencoder_Object
{
    public function __construct($params)
    {
        $this->_update_attributes($params);
    }

    private function _update_attributes($attributes = array())
    {
        foreach($attributes as $attr_name => $attr_value) {
            if(empty($this->$attr_name)) $this->$attr_name = $attr_value;
        }
    }

    protected function _create_outputs($outputs = array())
    {
        foreach($outputs as $output_attrs) {
            if(!empty($output_attrs->label)) {
                $this->outputs[$output_attrs->label] = new Services_Zencoder_Output($output_attrs);
            } else {
                $this->outputs[] = new Services_Zencoder_Output($output_attrs);
            }
        }
    }
}
