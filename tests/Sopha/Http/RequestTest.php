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
 * @version    $Id: Exception.php 2 2008-03-20 21:58:05Z shahar.e $
 * @license    http://prematureoptimization.org/sopha/license/new-bsd 
 */

require_once '../testConfiguration.php';
require_once 'Sopha/Http/Request.php';
require_once 'PHPUnit/Framework/TestCase.php';

/**
 * Sopha_Http_Request test case.
 */
class Sopha_Http_RequestTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Sopha_Http_Request
     */
    private $_url;
    
    /**
     * Prepares the environment before running a test.
     */
    protected function setUp ()
    {
        if (defined('ST_TESTDB_URL') && ST_TESTDB_URL) {
            $this->_url = ST_TESTDB_URL;
        } else {
            $this->markTestSkipped('No CouchDB server was set up');
        }
    }
    
    /**
     * Tests Sopha_Http_Request->addQueryParam()
     */
    public function testAddQueryParam ()
    {
        // TODO Auto-generated Sopha_Http_RequestTest->testAddQueryParam()
        $this->markTestIncomplete("addQueryParam test not implemented");
        $this->Sopha_Http_Request->addQueryParam(/* parameters */);
    }
    /**
     * Tests Sopha_Http_Request::delete()
     */
    public function testDelete ()
    {
        // TODO Auto-generated Sopha_Http_RequestTest::testDelete()
        $this->markTestIncomplete("delete test not implemented");
        Sopha_Http_Request::delete(/* parameters */);
    }
    /**
     * Tests Sopha_Http_Request::get()
     */
    public function testGet ()
    {
        // TODO Auto-generated Sopha_Http_RequestTest::testGet()
        $this->markTestIncomplete("get test not implemented");
        Sopha_Http_Request::get(/* parameters */);
    }
    /**
     * Tests Sopha_Http_Request::post()
     */
    public function testPost ()
    {
        // TODO Auto-generated Sopha_Http_RequestTest::testPost()
        $this->markTestIncomplete("post test not implemented");
        Sopha_Http_Request::post(/* parameters */);
    }
    /**
     * Tests Sopha_Http_Request::put()
     */
    public function testPut ()
    {
        // TODO Auto-generated Sopha_Http_RequestTest::testPut()
        $this->markTestIncomplete("put test not implemented");
        Sopha_Http_Request::put(/* parameters */);
    }
    /**
     * Tests Sopha_Http_Request->send()
     */
    public function testSend ()
    {
        // TODO Auto-generated Sopha_Http_RequestTest->testSend()
        $this->markTestIncomplete("send test not implemented");
        $this->Sopha_Http_Request->send(/* parameters */);
    }
    /**
     * Tests Sopha_Http_Request->__construct()
     */
    public function test__construct ()
    {
        // TODO Auto-generated Sopha_Http_RequestTest->test__construct()
        $this->markTestIncomplete("__construct test not implemented");
        $this->Sopha_Http_Request->__construct(/* parameters */);
    }
}

