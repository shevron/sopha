<?php

/**
 * Sopha - A PHP 5.x Interface to CouchDB 
 * 
 * @package    Sopha
 * @subpackage Http
 * @author     Shahar Evron
 * @version    $Id$
 * @license    LICENSE.txt - New BSD License 
 */

class Sopha_Http_Response
{
    protected $vesion   = null;
    
    protected $code     = null;
    
    protected $message  = null;
    
    protected $headers  = array();
    
    protected $body     = null;
    
    protected $document = null;
    
    /**
     * Create a new response object 
     *
     * @param string $status
     * @param array  $headers
     * @param string $body
     */
    public function __construct($status, array $headers, $body)
    {
        if (! (preg_match('|^HTTP/(\S+)\s+(\S+)\s+(.+)$|', $status, $match))) {
            require_once 'Sopha/Http/Exception.php';
            throw new Sopha_Http_Exception("Unable to parse HTTP stataus line: '$status'");
        }
        
        $this->version = $match[1];
        $this->code    = $match[2];
        $this->message = $match[3];
        
        $this->headers = $headers;
        $this->body    = $body;
    }
    
    /**
     * Get the document returned in the body (JSON decoded)
     *
     * @return mixed
     */
    public function getDocument()
    {
        if (! $this->document) {
            require_once 'Zend/Json.php';
            $this->document = Zend_Json::decode($this->body);
        }

        return $this->document;
    }
    
    /**
     * Get the response body as string
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }
    
    /**
     * Get the HTTP status code
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->code;
    }
    
    /**
     * Get the HTTP response message (eg. "Not Found")
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
    
    /**
     * Get all headers - as an array or as string
     *
     * @param  boolean $asString
     * @return array|string
     */
    public function getAllHeaders($asString = false)
    {
        if ($asString) {
            $str = "";
            foreach($this->headers as $k => $v) {
                $str .= ucfirst($k) . ": " . $v . "\r\n";
            }
            return $str;
        } else {
            return $this->headers;
        }
    }
    
    /**
     * Get a specific HTTP response header
     *
     * @param  string $header
     * @return string
     */
    public function getHeader($header)
    {
        $header = strtolower($header);
        return isset($this->headers[$header]) ? $this->header[$header] : null;
    }
    
    /**
     * Get the HTTP response version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }
    
    /**
     * Check whether or not the response is a success
     *
     * @return boolean
     */
    public function isSuccess()
    {
        return ((int) $this->code / 100 == 2);
    }
    
    /**
     * Convert the response object to a string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getAllHeaders(true) . "\r\n" . $this->getBody(); 
    }
}