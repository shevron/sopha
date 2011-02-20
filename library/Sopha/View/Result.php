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
 * @subpackage View
 * @version    $Id$
 * @license    http://prematureoptimization.org/sopha/license/new-bsd 
 */

class Sopha_View_Result implements Countable, ArrayAccess, SeekableIterator 
{
    /**
     * Possible return types
     */
    const RETURN_ARRAY     = 1;
    const RETURN_JSON      = 2;
    const RETURN_OBJECT    = 4;
    
    protected $_metadata    = array();

    protected $_values      = array();
    
    protected $_pointer     = 0;
    
    protected $_return_type = 1;
    
    protected $_doc_class   = null;
    
    public function __construct(array $result, $return = self::RETURN_ARRAY)
    {
        if (! isset($result['rows'])) {
            require_once 'Sopha/View/Result/Exception.php';
            throw new Sopha_View_Result_Exception("Result does not seem to be a " . 
                "valid view result data");
        }
        
        if (is_array($result['rows'])) {
            $this->_values = $result['rows'];
        }
        
        unset($result['rows']);
        
        $this->_metadata = $result;
        
        $this->_return_type = $return;
        
        if ($return == self::RETURN_JSON) {
            require_once 'Sopha/Json.php';
            
        } elseif (is_string($return)) {
            require_once 'Zend/Loader.php';
            try {
                Zend_Loader::loadClass($return);
                
                // Make sure that class is a subclass of Sopha_Document
                // We do that now instead of in run-time
                $testObj = new $return;
                if (! $testObj instanceof Sopha_Document) {
                    require_once 'Sopha/View/Result/Exception.php';
                    throw new Sopha_View_Result_Exception("$return is not as subclass of " . 
                        "Sopha_Document as expected");
                }
                
                $this->_doc_class = $return;
                $this->_return_type = self::RETURN_OBJECT;
                
            } catch (Zend_Exception $e) {
                require_once 'Sopha/View/Result/Exception.php';
                throw new Sopha_View_Result_Exception("Unable to load class $return");
            }
            
        } elseif ($return != self::RETURN_ARRAY) {
            require_once 'Sopha/View/Result/Exception.php';
            throw new Sopha_View_Result_Exception("Invalid return type: $return");
        }
    }
    
    /**
     * Return the document at the specified offset according to the return type
     * 
     * @param  integer $offset
     * @return mixed
     */
    protected function _returnDoc($offset)
    {
        $ret = null;
        
        if (isset($this->_values[$offset])) {
            $data = $this->_values[$offset]['value'];
            if ($data === null && isset($this->_values[$offset]['doc'])) {
                $data = $this->_values[$offset]['doc'];
            }
 
            switch($this->_return_type) {
                case self::RETURN_ARRAY:
                    $ret =  $data; 
                    break;
                    
                case self::RETURN_JSON:
                    $ret =  Sopha_Json::encode($data);
                    break;
                    
                case self::RETURN_OBJECT:
                    $ret =  new $this->_doc_class($data);
                    break;
            }
                
        } else {
            throw new OutOfBoundsException('Pointer points to invalid index');
        }
        
        return $ret;
    }
    
    /**
     * Get the key of the current view item
     *
     * @return string
     */
    public function currentKey()
    {
        if (isset($this->_values[$this->_pointer]['key'])) {
            return $this->_values[$this->_pointer]['key'];
        } else {
            return null;
        }
    }
    
    /**
     * Get the ID of the document that emitted the current view item
     *
     * @return string
     */
    public function currentId()
    {
        if (isset($this->_values[$this->_pointer]['id'])) {
            return $this->_values[$this->_pointer]['id'];
        } else {
            return null;
        }
    }
    
    /**
     * SPL Countable Interface
     */
    
    /**
     * Count the number of rows in the result
     * 
     * @return integer
     */
    public function count()
    {
        return count($this->_values);
    }
    
    /**
     * SPL SeekableIterator Interface (inherits from Iterator)
     */
    
    /**
     * Get the current element
     * 
     * @return mixed
     */
    public function current()
    {
        return $this->_returnDoc($this->_pointer);
    }
    
    public function key()
    {
        return $this->_pointer;
    }
    
    public function next()
    {
        $this->_pointer += 1;
    }
    
    public function rewind()
    {
        $this->_pointer = 0;
    }
    
    public function valid()
    {
        return isset($this->_values[$this->_pointer]);
    }
    
    public function seek($index)
    {
        if (isset($this->_values[$index])) {
            $this->_pointer = $index; 
        } else {
            throw new OutOfBoundsException('Invalid Seek Position');
        }
    }
    
    /**
     * SPL ArrayAccess Interface
     */
    
    public function offsetExists($offset)
    {
        return isset($this->_values[$offset]);
    }
    
    public function offsetGet($offset)
    {
        try {
            return $this->_returnDoc($offset);
        } catch (OutOfBoundsException $e) {
            return null;
        }
    }
    
    public function offsetSet($offset, $value)
    {
        require_once 'Sopha/View/Result/Exception.php';
        throw new Sopha_View_Result_Exception("Trying to write to read-only result set");
    }
    
    public function offsetUnset($offset)
    {
        require_once 'Sopha/View/Result/Exception.php';
        throw new Sopha_View_Result_Exception("Trying to write to read-only result set");
    }
    
    /**
     * Metadata access overloading
     */
    
    public function __get($key) 
    {
        if (isset($this->_metadata[$key])) {
            return $this->_metadata[$key];
        } else {
            return null;
        }
    }
    
    public function __isset($key)
    {
        return isset($this->_metadata[$key]);
    }
    
    public function __set($key, $value)
    {
        require_once 'Sopha/View/Result/Exception.php';
        throw new Sopha_View_Result_Exception("Trying to write to read-only result set");
    }
    
    public function __unset($key) 
    {
        require_once 'Sopha/View/Result/Exception.php';
        throw new Sopha_View_Result_Exception("Trying to write to read-only result set");
    }
}
