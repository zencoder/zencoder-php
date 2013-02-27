<?php
/**
 * Zencoder API client interface.
 *
 * @category Services
 * @package  Services_Zencoder
 * @author   Michael Christopher <m@zencoder.com>
 * @license  http://creativecommons.org/licenses/MIT/MIT MIT
 * @version  Release: 2.1.2
 * @link     http://github.com/zencoder/zencoder-php
 */

class Services_Zencoder_Progress extends Services_Zencoder_Object
{
    public function __construct($params)
    {
        $this->_updateAttributes($params);
    }

    private function _updateAttributes($attributes = array())
    {
        foreach ($attributes as $attr_name => $attr_value) {
            if ($attr_name == "outputs" && is_array($attr_value)) {
                $this->_create_outputs($attr_value);
            } elseif ($attr_name == "input" && is_object($attr_value)) {
                $this->input = new Services_Zencoder_Input($attr_value);
            } elseif (empty($this->$attr_name)) {
                $this->$attr_name = $attr_value;
            }
        }
    }
}
