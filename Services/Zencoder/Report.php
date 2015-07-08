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
	 * Statistics of a report
	 * 
	 * @var object
	 */
	public $statistics;

	/**
	 * Totals of a of report
	 *
	 * @var object
	 */
	public $total;

	/**
	 * A copy of the raw API response for debug purposes
	 * 
	 * @var mixed
	 */
	protected $raw_response;

	/**
	 * Create a new Services_Zencoder_Report object.
	 * For attributes of the various kinds of reports, see
	 * @link https://app.zencoder.com/docs/api/reports/vod
	 * @link https://app.zencoder.com/docs/api/reports/live
	 * @link https://app.zencoder.com/docs/api/reports/all
	 *
	 * @param mixed $params API response
	 * @param string $type The type of statistic we are fetching
	 */
	public function __construct($params) {
		$this->raw_response = $params;
		parent::__construct($params);
	}

}
