<?php

/**
 * Zencoder API client interface.
 *
 * @category Services
 * @package  Services_Zencoder
 * @author   Cyril Tata <cyril.tata@ekspressdigital.eu>
 * @version  Release: 2.1.2
 * @license  http://creativecommons.org/licenses/MIT/MIT
 * @link     http://github.com/zencoder/zencoder-php
 */
class Services_Zencoder_Report extends Services_Zencoder_Object {

	/**
	 * Array for statistics of the report
	 * 
	 * @var array
	 */
	public $statistics = array();

	/**
	 * Holds the totals in case of single type of report
	 *
	 * @var mixed (Services_Zencoder_Report_VodTotal|Services_Zencoder_Report_LiveTotal)
	 */
	public $total;

	/**
	 * A copy of the raw API response for debug purposes
	 * 
	 * @var mixed
	 */
	protected $raw_response;

	/**
	 * Flag to check if you will be filling array of arrays (when type = 'all')
	 *
	 * @var boolean
	 */
	protected $is_multiple = false;

	/**
	 * The type of report that is being fetched ('live', 'vod' 'minutes' or 'all')
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Create a new Services_Zencoder_Job object.
	 *
	 * @param mixed $params API response
	 * @param string $type The type of statistic we are fetching
	 */
	public function __construct($params, $type) {
		$this->raw_response = $params;
		$this->type = $type;
		$this->is_multiple = $this->type === "all";
		$this->_update_attributes($params);
	}

	private function _update_attributes($attributes = array()) {
		foreach ($attributes as $attr_name => $attr_value) {
			if (($attr_name == "statistics")) {
				if (!$this->is_multiple) {
					$this->_create_statistics($attr_value, $this->type);
				} else {
					foreach ($attr_value as $type => $attrs) {
						$this->_create_statistics($attrs, $type);
					}
				}
			} elseif (($attr_name == "total")) {
				if (!$this->is_multiple) {
					$this->_create_totals($attr_value, $this->type);
				} else {
					foreach ($attr_value as $type => $attrs) {
						$this->_create_totals($attrs, $type);
					}
				}
				
			}
		}
	}

	private function _create_statistics($statistics = array(), $type = null) {
		$class = $this->_get_report_class('statistic', $type);
		if ($this->is_multiple) {
			$this->statistics[$type] = array();
		}

		foreach ($statistics as $stat_attrs) {
			if ($this->is_multiple) {
				$this->statistics[$type][] = new $class($stat_attrs);
			} else {
				$this->statistics[] = new $class($stat_attrs);
			}
		}
	}

	private function _create_totals($totals, $type = null) {
		$class = $this->_get_report_class('total', $type);
		if ($this->is_multiple) {
			$this->total[$type] = new $class($totals);
		} else {
			$this->total = new $class($totals);
		}
	}

	private function _get_report_class($attr_name, $type) {
		return 'Services_Zencoder_Report_' . ucwords($type) . ucwords($attr_name);
	}

}
