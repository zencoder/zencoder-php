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
class Services_Zencoder_Reports extends Services_Zencoder_Base {

	/**
	 * A list of valid 'methods' to be trapped in __call()
	 *
	 * @link @link https://app.zencoder.com/docs/api/reports/
	 * @var array
	 */
	protected $methods = array('vod', 'live', 'minutes', 'all');

	/**
	 * Return all reports for VOD
	 *
	 * @link https://app.zencoder.com/docs/api/reports
	 *
	 * @param string $method The method name indicates the type of report we want to get
	 * @param array $args A list of arguments for the overriden methods. Each methods takes 2 arguements.
	 *					  The first being an associative array of query string parameters and the second
	 *					  an associative array of option overrides
	 * 
	 * @throws Services_Zencoder_Exception
	 *
	 * @return Services_Zencoder_Report The object representation of the resource
	 */
	public function __call($method, $args) {
		if (!in_array($method, $this->methods)) {
			throw new Services_Zencoder_Exception("Unsupported method call '$method' for Services_Zencoder_Reports");
		}

		// initialize query string parameters and optional overrides
		$params = $opts = array();

		// set query string parameters
		if (isset($args[0]) && is_array($args[0])) {
			$params = $args[0];
		}

		// set optional overrides
		if (isset($args[1]) && is_array($args[1])) {
			$opts = $args[1];
		}

		return new Services_Zencoder_Report($this->proxy->retrieveData("reports/$method", $params, $opts), $method);
	}

}
