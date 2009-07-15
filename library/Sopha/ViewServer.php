<?php

require_once 'Sopha/ViewServer/MapFunction.php';

class Sopha_ViewServer
{
	/**
	 * View functions
	 * 
	 * @var array
	 */
	protected $_functions = array();

	/**
	 * Output stream
	 * 
	 * @var resource
	 */
	protected $_outStream = STDOUT;
	
	/**
	 * Constructor. Also starts cyclic garbage collection.
	 * 
	 * @return void
	 */
	public function __construct()
	{
		gc_enable();
	}
	
	/**
	 * Run the server. This is the main loop of the server.
	 * 
	 * @param  mixed $stream Optional input stream
	 * @return void
	 */
	public function run($stream = null)
	{
		if (! is_resource($stream)) {
			if (! $stream) {
				$stream = fopen('php://stdin', 'r');
			} else {
				$stream = fopen($stream, 'r');
			}
		}
		
		while ($line = fgets($stream)) {
			
			$cmd = json_decode($line);
			if (count($cmd) < 1) {
				$this->log('Error parsing command');
				continue;
			}
			
			switch($cmd[0]) {
				case 'reset':
                    $this->_reset();
                    break;
                    
				case 'add_fun':
                    $this->_add_fun($cmd[1]);
                    break;
                    
				case 'map_doc':
					$this->_map_doc($cmd[1]);
					break;
					
				/*	
				case 'reduce':
					$this->_reduce($cmd[1], $cmd[2]);
					break;
					
				case 'rereduce':
					$this->_rereduce()
					break;
                */
					
				default:
					$this->log('Unknown command: ' . $cmd[0]);
					break;
			}
		}		
		
		fclose($stream);
	}

	/**
	 * Set the output stream. By default, messages are sent to STDOUT
	 * 
	 * @param  mixed $stream
	 * @return void
	 */
	public function setOutputStream($stream)
	{
		if (is_resource($stream)) {
			$this->_outStream = $stream;
		} elseif ($stream) {
			$this->_outStream = fopen($stream, 'w');
		} else {
			$this->_outStream = STDOUT; 
		}
	}
	
	/**
	 * Send a message to CouchDB's log
	 * 
	 * @param  string $message
	 * @return void
	 */
    public function log($message)
    {
    	$this->_send(array('log' => (string) $message));
    }

    /**
     * Reset the view server. Will clear all saved functions and run garbage collection.
     * 
     * @return void
     */
    protected function _reset()
    {
    	$this->_functions = array();
    	gc_collect_cycles();
    	$this->_send(true);
    }
    
    /**
     * Add a view function 
     * 
     * @param  string $code
     * @return void
     */
    protected function _add_fun($code)
    {
    	try {
            $func = new Sopha_ViewServer_MapFunction($code, $this);
            $this->_functions[] = $func;
            $this->_send(true);
            
    	} catch (Sopha_ViewServer_Exception $e) {
    		$this->log($e->getMessage());
    		$this->_send(array('error' => 'compile_error', 'reason' => $e->getMessage()));
    	}
    }
    
    /**
     * Map a document
     * 
     * @param  mixed $doc
     * @return void
     */
    protected function _map_doc($doc)
    {
    	$data = array();
    	foreach($this->_functions as $func) {
    		$data[] = $func($doc);
    	}
    	
    	$this->_send($data);
    }
    
    /**
     * Send the data back to CoudhDB
     * 
     * @param  mixed $data
     * @return void
     */
    protected function _send($data)
    {
    	fprintf($this->_outStream, "%s\n", json_encode($data));
    }
}