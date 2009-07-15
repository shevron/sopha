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
require_once 'Sopha/ViewServer/MapFunction.php';

require_once 'PHPUnit/Framework/TestCase.php';

/**
 * Sopha_ViewServer_MapFunction test case.
 */
class Sopha_ViewServer_MapFunctionTest extends PHPUnit_Framework_TestCase 
{	
	/**
	 * Tests the constructor with some valid PHP functions
	 * 
	 * @dataProvider validFunctionsDataProvider
	 */
	public function testCreateNewValidFunction($code) 
	{
		$server = new Sopha_ViewServer();
		$func = new Sopha_ViewServer_MapFunction($code, $server);

		$this->assertTrue(is_callable($func));
	}
	
	/**
	 * Tests that invoking a valid map function on a doc produces array 
	 * of emitted docs
	 * 
	 * @dataProvider documentProvider
	 */
	public function testInvokeValidFunction($docStr) 
	{
		$funcs = self::validFunctionsDataProvider();
		$server = new Sopha_ViewServer();
		
		foreach($funcs as $code) {
            $func = new Sopha_ViewServer_MapFunction($code[0], $server);
            $doc = json_decode($docStr);
            
            $out = $func($doc);
            $this->assertType('array', $out);
		}
	}

	/**
	 * Data provider of valid PHP map functions
	 * 
	 * @return array
	 */
	static public function validFunctionsDataProvider()
	{
		return array(
            array('function($doc) { $a = 1 + 1; }'),
            array('function($doc) { $view->emit($doc->_id, $doc); }'),
            array('function($doc) { if ($doc->type == "User") { $view->emit($doc->email, $doc->fullname); } }')		
		);
	}
	
	static public function documentProvider()
	{
		return array(
            array(json_encode(array(
                '_id'     => '1234', 
                'type'    => 'Page',
                'title'   => 'This is some page', 
                'content' => array('a', 'b', 'b')
            ))),
            array(json_encode(array(
                '_id'      => '2345',
                'type'     => 'User',
                'fullname' => "John Doe",
                'email'    => 'john@example.com'
            ))),
            array(json_encode(array(
                '_id'      => '3456',
                'type'     => 'User',
                'fullname' => 'Joe Shmoe',
                'email'    => 'bobo@foo.bar'
            )))
        );
	}
}