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
 * @subpackage Document
 * @version    $Id$
 * @license    http://prematureoptimization.org/sopha/license/new-bsd 
 */

/**
 * Sopha Document Attachment class
 * 
 * Sopha_Document_Attachment objects are simple container objects that 
 * represent attachments to CouchDb documents
 *
 */
class Sopha_Document_Attachment
{
    /**
     * Parent document URL (including server)
     * 
     * @var string 
     */
    protected $docUrl = null;
    
    /**
     * Attachment name
     * 
     * @var string
     */
    protected $name = null;
    
    /**
     * Attachment MIME type
     * 
     * @var string
     */
    protected $type = null;
    
    /**
     * Attachment data (actual bytes)
     * 
     * @var string
     */
    protected $data = null;
    
    /**
     * Attachment size in bytes
     * 
     * @var integer
     */
    protected $size = null;
    
    /**
     * Create a new Attachment object
     * 
     * @param string $docUrl
     * @param string $name
     * @param string $type
     * @param string $data
     */
    public function __construct($docUrl, $name, $type = null, $data = null)
    {
        $this->docUrl = $docUrl;
        $this->name   = $name;
        
        if ($type) {
            if (! $data) {
                require_once 'Sopha/Document/Exception.php';
                throw new Sopha_Document_Exception("Attachment cannot be created with type and no data");
            }
            $this->type = $type;
        }
        
        if ($data) {
            if (! $type) {
                require_once 'Sopha/Document/Exception.php';
                throw new Sopha_Document_Exception("Attachment cannot be created with data and no type");
            }
            $this->data = $data;
            $this->size = strlen($data);
        }
    }
    
    /**
     * Get the attachment's name
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the attachment's parent document's URL
     * 
     * @return string
     */
    public function getParentDocUrl()
    {
        return $this->docUrl;
    }
    
    /**
     * Get the attachment's MIME type. Will lazy-load from DB if not yet loaded
     * 
     * @return string
     */
    public function getMimeType()
    {
        if ($this->type === null) $this->_lazyLoadData();
        return $this->type;
    }
    
    /**
     * Get the actual data.  Will lazy-load from DB if not yet loaded
     * 
     * @return string
     */
    public function getData()
    {
        if ($this->data === null) $this->_lazyLoadData();
        return $this->data;
    }
    
    /**
     * Get the size of the attachment in bytes
     * 
     * @return integer
     */
    public function getSize()
    {
        if ($this->size) {
            return $this->size;
        } else {
            return strlen($this->getData());
        }
    }
    
    /**
     * Send the attachment as an HTTP response directly to output
     * 
     * Will set the content-type and content-length headers and send the 
     * attachment data directly to the user as response body. This provides 
     * less control over content - but is a far more memory efficient method
     * to send attachments directly to the user
     * 
     * @todo   Finish implementing the stuff here!
     * 
     * @return void 
     */
    public function passthru()
    {
        if (headers_sent()) {
            require_once 'Sopha/Document/Exception.php';
            throw new Sopha_Document_Exception("Can't passthru attachment: headers already sent");
        }
        
        if ($this->data) {
            header("Content-type: " . $this->getMimeType());
            echo $this->data;
        } else {
            // TODO: This is where passthru should be implemented!
            header("Content-type: " . $this->getMimeType());
            echo $this->getData();
        }
    }
    
    /**
     * Lazy-load the Attachment data if it was not already loaded
     */
    protected function _lazyLoadData()
    {
        require_once 'Sopha/Http/Request.php';
        
        $request = new Sopha_Http_Request($this->docUrl . '/' . $this->name);
        $response = $request->send();

        switch($response->getStatus()) {
            case 200:
                $this->data = $response->getBody();
                $this->type = $response->getHeader('content-type');
                $this->size = $response->getHeader('content-length'); 
                break;
                
            case 404:
                require_once 'Sopha/Document/Exception.php';
                throw new Sopha_Document_Exception("Attachment '$this->name' does not exist", $response->getStatus());
                break;
                
            default:
                require_once 'Sopha/Db/Exception.php';
                throw new Sopha_Db_Exception("Unexpected response from server: " . 
                    "{$response->getStatus()} {$response->getMessage()}", $response->getStatus());
                break;
        }
    }
    
    /**
     * Create a new attachment object from HTTP response
     * 
     * @param  string                    $docUrl
     * @param  string                    $name
     * @param  Sopha_Http_Response       $response
     * @return Sopha_Document_Attachment
     */
    static public function fromReponse($docUrl, $name, Sopha_Http_Response $response)
    {
         $att = new Sopha_Document_Attachment($docUrl, $name, 
            $response->getHeader('content-type'), $response->getBody());
            
         return $att;
    }
}
