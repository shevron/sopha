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
 * @category   Tests
 * @version    $Id$
 * @license    http://prematureoptimization.org/sopha/license/new-bsd 
 */

// Load the test helper
require_once dirname(__FILE__) . '/../../TestHelper.php';

require_once 'Sopha/ViewServer.php';

require_once 'PHPUnit/Framework/TestCase.php';

/**
 * Sopha_ViewServer_MapFunction test case.
 */
class Sopha_ViewServer_ServerTest extends PHPUnit_Framework_TestCase 
{
	/**
	 * View Server
	 * 
	 * @var Sopha_ViewServer
	 */
	protected $_server = null;

	protected $_stdout = null;

	/**
     * Set up the test environment
     * 
	 */
	public function setUp()
	{
		$this->_server = new Sopha_ViewServer();
		
		$this->_stdout = fopen('php://temp', 'r+');
		$this->_server->setOutputStream($this->_stdout);
	}
	
	/**
     * Tear down the test environment
	 */
	public function tearDown()
	{
		unset($this->_server);
		fclose($this->_stdout);
	}

	/**
	 * Test Cases 
	 */
	
	/**
     * Test the server's ability to send messages to the log
     * 
     * @param  string $message
     * @dataProvider messageStringProvider 
     */
    public function testLog($message)
    {
        $this->_server->log($message);
        
        $logged = $this->_getOutput();
        
        $expect = json_encode(array("log" => $message)) . "\n";
        $this->assertEquals($expect, $logged);
    }

    /**
     * Test that one can add functions
     * 
     */
	public function testAddFunction()
	{
		$this->assertEquals(0, count($this->getObjectAttribute($this->_server, '_functions')));
		
		$funcs = array(
            'function($doc){ $a = 1 + 2; }',
            'function($doc){ $view->emit($doc->id, $doc->title); }',
            'function($doc){ if ($doc->{"@doctype"} == "Page") { $view->emit($doc->normalized_title, $doc->_id);$view->log("Some message!");}}'
        );
		
		// Prepare input stream
		$inputStr = '';
		foreach($funcs as $fText) {
			$inputStr .= json_encode(array('add_fun', $fText)) . "\n";
		}
		$inStream = 'data://text/plain;base64,' . base64_encode($inputStr);

		$this->_server->run($inStream);
		
		$this->assertEquals(3, count($this->getObjectAttribute($this->_server, '_functions')));
		$this->assertEquals("true\ntrue\ntrue\n", $this->_getOutput());
	}

	/**
	 * Test that reseting the server clears all functions and returns true
	 * 
	 */
    public function testResetServer()
    {
        $this->assertEquals(0, count($this->getObjectAttribute($this->_server, '_functions')));
        
        $funcs = array(
            'function($doc){ $a = 1 + 2; }',
            'function($doc){ $view->emit($doc->id, $doc->title); }'
        );
        
        // Prepare input stream
        $inputStr = '';
        foreach($funcs as $fText) {
            $inputStr .= json_encode(array('add_fun', $fText)) . "\n";
        }
        $inStream = 'data://text/plain;base64,' . base64_encode($inputStr);

        $this->_server->run($inStream);

        $this->assertEquals(2, count($this->getObjectAttribute($this->_server, '_functions')));
        
        // Send the reset command
        $inputStr = json_encode(array('reset')) . "\n";
        $inStream = 'data://text/plain;base64,' . base64_encode($inputStr);
        
        $this->_server->run($inStream);
        
        $this->assertEquals(0, count($this->getObjectAttribute($this->_server, '_functions')));
        $this->assertEquals("true\ntrue\ntrue\n", $this->_getOutput());
    }
    
    /**
     * Test that calling MapDoc produces the correct output
     * 
     * @dataProvider documentProvider 
     * @param string $docStr
     */
    public function testMapDoc($docStr)
    {
        $funcs = array(
            'function($doc){ $a = 1 + 2; }',
            'function($doc){ $view->emit($doc->_id, $doc->title); }',
            'function($doc){ if ($doc->type == "Page") { $view->emit(array($doc->_id, $doc->title), $doc->content); $view->log("Some message!");}}'
        );
        
        // Prepare input stream
        $inputStr = '';
        foreach($funcs as $fText) {
            $inputStr .= json_encode(array('add_fun', $fText)) . "\n";
        }
        $inStream = 'data://text/plain;base64,' . base64_encode($inputStr);

        $this->_server->run($inStream);
    	
        $this->assertEquals(3, count($this->getObjectAttribute($this->_server, '_functions')));
        
        $inputStr = json_encode(array('map_doc', $docStr)) . "\n";
        $inStream = 'data://text/plain;base64,' . base64_encode($inputStr);
        
        $this->_server->run($inStream);
        
        // Assert something here!
    }
    
    static public function messageStringProvider()
    {
    	return array(
    	   array("This is some test message"),
    	   array("This is another test message"),
    	   array("הנה הודעה בעברית, מה תגידו על זה?")
    	);
    }
    
    static public function documentProvider()
    {
        return array(
            array(array(
                '_id'     => '1234', 
                'type'    => 'Page',
                'title'   => 'This is some page', 
                'content' => array('a', 'b', 'b')
            )),
            array(array(
                '_id'      => '2345',
                'type'     => 'User',
                'fullname' => "John Doe",
                'title'    => 'Mr.',
                'email'    => 'john@example.com'
            )),
            array(array(
                '_id'      => '3456',
                'type'     => 'User',
                'fullname' => 'Joe Shmoe',
                'title'    => 'Prof.',
                'email'    => 'bobo@foo.bar'
            ))
        );
    }
    
    /**
     * Get the contents of the output stream
     * 
     * @return unknown_type
     */
    protected function _getOutput()
    {
    	rewind($this->_stdout);
    	return stream_get_contents($this->_stdout);
    }
    
    
}