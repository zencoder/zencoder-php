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
     * Get details about different types of reports
     *
     * @link https://app.zencoder.com/docs/api/reports
     *
     * @param string $report_type The type of report to get. The following are currently supported over the api
     * 							  - 'all' : Returns all reports, both VOD and LIVE
     * 							  - 'vod' : Returns VOD reports
     * 							  - 'live': Return LIVE reports
     * @param array $params An associated array of optional query parameters per requested report type
     * @param array $opts Optional overrides
     *
     * @return Services_Zencoder_Report The object representation of the resource
     */
    public function details($report_type = 'all', $params = array(), $opts = array()) {
        return new Services_Zencoder_Report($this->proxy->retrieveData("reports/$report_type", $params, $opts));
    }

}
