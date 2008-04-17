<?php

/**
 * Sopha library - HTTP request class tests
 *
 * @package  Sopha 
 * @category tests
 * @author   Shahar Evron
 * @version  $Id: Db.php 4 2008-03-28 18:30:35Z shahar.e $
 * @license  LICENSE.txt - New BSD License 
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

