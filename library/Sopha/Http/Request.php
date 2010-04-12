<?php

/**
 * Sopha - A PHP 5.x Interface to CouchDB
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * http://prematureoptimization.org/sopha/license/new-bsd
 * 
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @package    Sopha
 * @subpackage Http
 * @version    $Id$
 * @license    http://prematureoptimization.org/sopha/license/new-bsd 
 */

require_once 'Sopha/Http/Response.php';

class Sopha_Http_Request
{
    /**
     * HTTP request methods
     */
    const GET    = 'GET';
    const POST   = 'POST';
    const PUT    = 'PUT';
    const DELETE = 'DELETE';
    
    const HTTP_VER = '1.1';
    
    /**
     * URL
     * 
     * @var string
     */
    protected $_url;
    
    /**
     * Request method (one of the constants above)
     * 
     * @var string
     */
    protected $_method;
    
    /**
     * Request headers
     * 
     * @var array
     */
    protected $_headers = array();
    
    /**
     * Query parameters
     *  
     * @var array
     */
    protected $_query = array();
    
    /**
     * Request data
     * 
     * @var string
     */
    protected $_data;
    
    /**
     * Socket stream resource for current connection
     * 
     * @var resource
     */
    protected $_socket = null;

    /**
     * Peer (host:port) we are curently connected to
     * 
     * @var string
     */
    protected $_peer = null;
    
    /**
     * Static array of all connections to servers
     *
     * @var array
     */
    static protected $_connections = array();
    
    /**
     * Create a new HTTP request object
     *
     * @todo  validation
     * 
     * @param string $url    URL to request
     * @param string $method HTTP request method: GET, POST, PUT, DELETE
     * @param string $data   HTTP request body to send
     */
    public function __construct($url, $method = self::GET, $data = null)
    {
        $this->_url    = $url;
        $this->_method = $method;
        $this->_data   = $data;
    }
    
    /**
     * Send the HTTP request, return an HTTP response
     *
     * @return Sopha_Http_Response
     */
    public function send()
    {
        $url = parse_url($this->_url);

        // Build query string
        if (isset($url['query'])) $this->_query = array_merge(
            parse_str($url['query']), $this->_query);
            
        if (! empty($this->_query)) {
            $url['query'] = http_build_query($this->_query);
        }
        
        // Build body and headers
        $body = $this->_buildBody();
        $headers = $this->_buildHeaders($url);
        
        // Send HTTP request and read response
        $this->_connect($url['host'], $url['port']);
        
        // Attempt to reconned if connection is lost
        // TODO: This is ugly and should be redone ;)
        try {
            $this->_write($headers . "\r\n" . $body);
            list($status, $headers, $body) = $this->_read();
        } catch (Sopha_Http_Exception $ex) {
            $this->_close();
            $this->_connect($url['host'], $url['port']);
            $this->_write($headers . "\r\n" . $body);
            list($status, $headers, $body) = $this->_read();
        }
        
        return new Sopha_Http_Response($status, $headers, $body);
    }
    
    /**
     * Add a parameter to the query string (part after the '?' in the URL)
     *
     * @param string $key
     * @param string $value
     */
    public function addQueryParam($key, $value)
    {
        $this->_query[(string) $key] = $value;
    }
    
    /***
     * Shortcut static methods
     **/
    
    /**
     * Send a simple GET request
     *
     * @param  string $url
     * @return Sopha_Http_Response
     */
    static public function get($url)
    {
        $request = new self($url);
        return $request->send();
    }
    
    /**
     * Send a simple POST request
     *
     * @param  string $url
     * @param  string $data
     * @return Sopha_Http_Response
     */
    static public function post($url, $data = '')
    {
        $request = new self($url, self::POST, $data);
        return $request->send();
    }
    
    /**
     * Send a simple PUT request
     *
     * @param  string $url
     * @param  string $data
     * @return Sopha_Http_Response
     */
    static public function put($url, $data = '')
    {
        $request = new self($url, self::PUT, $data);
        return $request->send();
    }
    
    /**
     * Send a simple DELETE request 
     *
     * @param  string $url
     * @return Sopha_Http_Response
     */
    static public function delete($url)
    {
        $request = new self($url, self::DELETE);
        return $request->send();
    }
    
    /**
     * Internal HTTP handling and connection methods
     */
    
    /**
     * Build HTTP request header string 
     *
     * @param  array $url parse_url() generated array
     * @return string
     */
    protected function _buildHeaders(array $url)
    {
        $path = $url['path'];
        if (isset($url['query'])) $path .= '?' . $url['query'];
        
        $headers = $this->_method . " " . $path . " " . 'HTTP/' . self::HTTP_VER . "\r\n";
        
        if (! isset($this->_headers['host']) && $url['host']) {
            $headers .= "Date: " . date(DATE_RFC822) . "\r\n";
        }
        
        foreach($this->_headers as $name => $header) {
            $headers .= $this->_buildHeadersRecursive($name, $header);
        }
        
        return $headers;
    }
    
    /**
     * Create the HTTP request body string
     *
     * @return string
     */
    protected function _buildBody()
    {
       if ($this->_method == self::GET    || 
           $this->_method == self::DELETE ||
           ! strlen($this->_data)) {
               
           return '';
       }
       
       $this->_headers['content-type'] = 'application/json';
       $this->_headers['content-length'] = strlen($this->_data);
       return $this->_data;
    }
    
    /**
     * Build a single header line or a set of header lines with the same name 
     * if an array was provided
     *
     * @param  string       $name 
     * @param  string|array $value
     * @return string
     */
    protected function _buildHeadersRecursive($name, $value)
    {
        $return = '';
        
        if (is_array($value)) {
            foreach ($value as $val) {
                $return .= $this->buildHeadersArray($name, $val);
            }
        } else {
            $return = ucfirst(strtolower($name)) . ": $value\r\n";
        }
        
        return $return;
    }
    
    /**
     * Connect to HTTP couch server
     *
     * @param string  $host
     * @param integer $port
     */
    protected function _connect($host, $port)
    {
        $peer = "$host:$port";
        
        if (isset(self::$_connections[$peer])) {
            $this->_socket = self::$_connections[$peer];
        }
        
        if (is_resource($this->_socket)) {
            return;
        }
         
        if (! ($this->_socket = fsockopen($host, $port, $errno, $errstr, 10))) {
            require_once 'Sopha/Exception.php';
            throw new Sopha_Exception("Error connecting to CouchDb server: [$errno] $errstr");
        }
        
        self::$_connections[$peer] = $this->_socket;
        $this->_peer = $peer;
    }
    
    /**
     * Send request data to HTTP couch server
     *
     * @param string $data
     */
    protected function _write($data)
    {
        if (! $this->_socket) {
            require_once 'Sopha/Exception.php';
            throw new Sopha_Exception("Lost connection to CouchDB server before sending data");
        }
        
        fwrite($this->_socket, $data);
    }
    
    /**
     * Read response from HTTP couch server
     *
     * @return array Array of (string status line, array headers, string body)
     */
    protected function _read()
    {
        if (! $this->_socket) {
            require_once 'Sopha/Http/Exception.php';
            throw new Sopha_Http_Exception("Lost connection to CouchDB server before reading response");
        }
        
        $status_line = null;
        $status_code = null;
		$headers = array();
		$last_header = null;        

        // First, read response headers and put them into an associative array
        while (($line = fgets($this->_socket))) {
            
            $line = trim($line);
        	if (! $status_line && strpos($line, 'HTTP/') === 0) {
        		$status_line = $line;
        		$status_code = (int) substr($status_line, 9, 3);
        		continue;
        	}
        	
        	if (! $line) break;

            if (preg_match("|^([\w-]+):\s+(.+)|", $line, $m)) {
                unset($last_header);
                
                $h_name = strtolower($m[1]);
                $h_value = $m[2];

                if (isset($headers[$h_name])) {
                    if (! is_array($headers[$h_name])) {
                        $headers[$h_name] = array($headers[$h_name]);
                    }

                    $headers[$h_name][] = $h_value;
                    end($headers[$h_name]);
                    $last_header = &$headers[$h_name][key($headers[$h_name])];
                    
                } else {
                    $headers[$h_name] = $h_value;
                    $last_header = &$headers[$h_name];
                }
                
            } elseif (preg_match("|^\s+(.+)$|", $line, $m) && $last_header !== null) {
                $headers[$last_header] .= $m[1];
            }
        }
        
        if (! $this->_socket || ! $status_line) {
            require_once 'Sopha/Http/Exception.php';
            throw new Sopha_Http_Exception("Unable to read HTTP response from server");
        }

        // Keep on reading the body - according to the headers
        $body = '';
        
        // Chunked transfer-encoding
        if (isset($headers['transfer-encoding'])) {
            if ($headers['transfer-encoding'] == 'chunked') {
                do {
                    $chunk = '';
                    $line = fgets($this->_socket);

                    $hexchunksize = ltrim(chop($line), '0');
                    $hexchunksize = strlen($hexchunksize) ? strtolower($hexchunksize) : 0;

                    $chunksize = hexdec(chop($line));
                    if (dechex($chunksize) != $hexchunksize) {
                        require_once 'Sopha/Http/Exception.php';
                        throw new Sopha_Http_Exception('Invalid chunk size "' . $hexchunksize . '" unable to read chunked body');
                    }

                    $left_to_read = $chunksize;
                    while ($left_to_read > 0) {
                        $line = @fread($this->_socket, $left_to_read);
                        $chunk .= $line;
                        $left_to_read -= strlen($line);
                    }

                    // Read the end of line after the chunk
                    fgets($this->_socket);
                    
                    $body .= $chunk;
                    
                } while ($chunksize > 0);
                
            } else {
                require_once 'Sopha/Http/Exception.php';
                throw new Sopha_Http_Exception('Cannot handle "' . $headers['transfer-encoding'] . '" transfer encoding');
            }

        // Specified content length to read
        } elseif (isset($headers['content-length'])) {
            $left_to_read = $headers['content-length'];
            $chunk = '';
            while ($left_to_read > 0) {
                $chunk = @fread($this->_socket, $left_to_read);
                $left_to_read -= strlen($chunk);
                $body .= $chunk;
            }
            
        // If code is 304 or 204 no body is expected
        } elseif ($status_code == 304 || $status_code == 204) {
            $body .= '';

        // Fallback: just read the response (should not happen)
        } else {
            while (($buff = @fread($this->_socket, 8192))) {
                $body .= $buff;
            }
        }
        
        return array($status_line, $headers, $body);
    }
    
    /**
     * Close connection to HTTP couch server
     *
     */
    protected function _close()
    {
        if ($this->_socket) {
            fclose($this->_socket);
        }
        
        if (isset($this->_peer) && isset(self::$_connections[$this->_peer])) {
            unset(self::$_connections[$this->_peer]);
            $this->_peer = null;
        }
    }
}