<?php

class Sopha_ViewServer_MapFunction
{
	protected $_func;
	
	protected $_server;
	
	protected $_docs = array();
	
	public function __construct($code, Sopha_ViewServer $server)
	{
		$this->_server = $server;
		
		// Manipulate code a bit..
		$view = $this;
		$code = preg_replace('/(function\s*\(.+?\))/', '\$this->_func = \1 use($view)', $code, 1);
		
		if (eval("$code;") === false || ! is_callable($this->_func)) {
			require_once 'Sopha/ViewServer/Exception.php';
			throw new Sopha_ViewServer_Exception("Unable to compile view function");
		}
	}

	public function log($message)
	{
		$this->_server->log($message);
	}
	
	public function emit($key, $value)
	{
		$this->_docs[] = array($key, $value);
	}
	
	public function __invoke($doc)
	{
		$func = $this->_func;
		$view = $this;
		
        $func($doc);
        
        $docs = $this->_docs;
        $this->_docs = array();
        
        return $docs;
	}
}
