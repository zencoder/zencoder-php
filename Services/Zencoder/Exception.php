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

class Services_Zencoder_Exception extends ErrorException
{
    protected $context;
    protected $errors;

    function __construct($message, $errors = null, $code = null, $severity = E_ERROR, $filename = null,
                         $lineno = null, array $context = array()) {
        parent::__construct($message, $code, $severity, $filename, $lineno);
        $this->errors = ($decode = json_decode($errors)) ? new Services_Zencoder_Error($decode->errors) : $errors;
        $this->context = $context;
    }

    /**
     * Return array that points to the active symbol table at the point the error
     * occurred. In other words, it will contain an array of every variable that
     * existed in the scope the error was triggered in.
     *
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Return array containing any errors returned from the code that threw the
     * exception.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
