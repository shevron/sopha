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

class Sopha_Document
{
    /**
     * Array of data
     *
     * @var array
     */
    protected $data     = array();
    
    /**
     * Array of metadata
     *
     * @var array
     */
    protected $metadata = array();
    
    /**
     * Document URL
     *
     * @var string
     */
    protected $url      = null;
    
    /**
     * Associated database object (if any)
     *
     * @var Sopha_Db
     */
    protected $db       = null;
    
    /**
     * Create a new document object
     *
     * @param array    $data
     * @param string   $url  The URL of this doc
     * @param Sopha_Db $db   The DB object this document belongs to
     */
    public function __construct(array $data = array(), $url = null, $db = null)
    {
        if ($db !== null) {
            if (! $db instanceof Sopha_Db) {
                require_once 'Sopha/Document/Exception.php';
                throw new Sopha_Document_Exception("\$db is expected to be a Sopha_Db object, got " . gettype($db));
            }
            
            $this->db = $db;
        }
        
        foreach($data as $k => $v) {
            if (substr($k, 0, 1) == '_') { 
                $this->metadata[$k] = $v;
            } else {
                $this->data[$k] = $v;
            }
        }
        
        // Set the URL
        if ($url) {
            $this->url = $url;
        } elseif (isset($data['_id'])) {
            $this->url = $db->getUrl() . urlencode($data['_id']);
        }
    }
    
    /**
     * Save document as new or modified document
     * 
     */
    public function save()
    {
        if (! isset($this->metadata['_id'])) { // Creating a new document
            $newDoc = $this->db->create($this->data, $this->url);
            
            $this->metadata['_id']  = $newDoc->getId();
            $this->metadata['_rev'] = $newDoc->getRevision();
            $this->url              = $newDoc->getUrl();
            
        } else { // Updating an existing document
            $this->db->update($this, $this->url);
        }
    }
    
    /**
     * Delete document from DB
     *
     */
    public function delete()
    {
        if (! $this->url) {
            require_once 'Sopha/Document/Exception.php';
            throw new Sopha_Document_Exception("Unable to delete a document without known URL");
        }
        
        require_once 'Sopha/Http/Request.php';
    }
    
    /**
     * Get the current document's revision (if known)
     *
     * @return string
     */
    public function getRevision()
    {
        return (isset($this->metadata['_rev']) ? $this->metadata['_rev'] : null); 
    }
    
    /**
     * Get the current document's ID (if known)
     *
     * @return string
     */
    public function getId()
    {
        return (isset($this->metadata['_id']) ? $this->metadata['_id'] : null);
    }
    
    /**
     * Get the current document's URL (if known)
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
    
    /**
     * Get the current document's array of attachments (if any)
     * 
     * This only returns information about the attachments - not the actual data
     *
     * @return array
     */
    public function getAttachments()
    {
        return (isset($this->metadata['_attachments']) ? $this->metadata['_attachments'] : array()); 
    }
    
    /**
     * Set an attachement to the document
     * 
     * Will either set a new attachment or replace an existing one with the 
     * same name
     * 
     * @param  string $name
     * @param  string $type Content type
     * @param  string $data  Attachement data
     */
    public function setAttachment($name, $type, $data)
    {
        if (! isset($this->metadata['_attachments'])) $this->metadata['attachments'] = array();

        $attachment = array(
            'content_type' => $type,
            'data'         => base64_encode($data)
        );
        
        $this->metadata['_attachments'][$name] = $attachment;
    }
    
    /**
     * Get one of the document's attachments as an Attachment object
     * 
     * @param  string $name Attachment name
     * @return Sopha_Document_Attachment or null if no such attachment 
     */
    public function getAttachment($name)
    {
        // Make sure the attachment is supposed to exist
        if (! isset($this->metadata['_attachments']) || 
            ! isset($this->metadata['_attachments'][$name])) {
            
            return null;
        }
        
        require_once 'Sopha/Document/Attachment.php';
        
        // Check if we have some non-saved attachment data
        if (isset($this->metadata['_attachments'][$name]['data'])) {
            return new Sopha_Document_Attachment($this->url, $name, 
                $this->metadata['_attachments'][$name]['content_type'],
                base64_decode($this->metadata['_attachments'][$name]['data']));
                 
        // Usually we dont - just return a stub Attachment object which will
        // lazy-load the data from DB. Requires a URL though.
        } else {
            if (! $this->url) {
                return null;
            }
                   
            return new Sopha_Document_Attachment($this->url, $name);
        }
    }
    
    /**
     * Convert the document to a string - will return a JSON encoded object
     *
     * @return string
     */
    public function __toString()
    {
        require_once 'Zend/Json.php';
        return Zend_Json::encode(array_merge($this->metadata, $this->data));
    }
    
    /**
     * Convert the document to an associative array
     * 
     * @param  boolean $metadata Whether to export metadata as well
     * @return array
     */
    public function toArray($metadata = false)
    {
        $data = $this->data;
        if ($metadata) $data = array_merge($data, $this->metadata);
        return $data;
    }
    
    /**
     * Load data from an associative array to document object
     * 
     * @param array $data
     */
    public function fromArray(array $data)
    {
        foreach($data as $k => $v) {
            if (substr($k, 0, 1) == '_') { 
                $this->metadata[$k] = $v;
            } else {
                $this->data[$k] = $v;
            }
        }
    }
    
    /**
     * Allow direct access to reading properties
     *
     * @param  string $key
     * @return mixed
     */
    public function __get($key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        } else {
            return null;
        }
    }
    
    /**
     * Allow direct access to writing document properties
     *
     * @param string $key
     * @param string $value
     */
    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }
    
    /**
     * Check if a document property exists
     *
     * @param  string $key
     * @return boolean
     */
    public function __isset($key)
    {
        return isset($this->data[$key]);
    }
    
    /**
     * Unset a document property if it exists
     *
     * @param string $key
     */
    public function __unset($key)
    {
        if (isset($this->data[$key])) unset($this->data[$key]);
    }
}
