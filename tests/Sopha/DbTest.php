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
require_once dirname(__FILE__) . '/../TestHelper.php';

require_once 'Sopha/Db.php';

/**
 * Sopha_Db test case
 * 
 */
class Sopha_DbTest extends PHPUnit_Framework_TestCase
{
    /**
     * Database URL to test against (taken from TestConfiguration.php)
     *
     * @var string
     */
    protected $_url;

    /**
     * Set up the test environment before every test
     *
     */
    protected function setUp()
    {
        parent::setUp();
        
        if (defined('SOPHA_TEST_DB_URL') && SOPHA_TEST_DB_URL) {
            $this->_url = SOPHA_TEST_DB_URL;
        } else {
            $this->_url = null;
        }
    }

    /*************************************************************************
     * Constructor and static tests
     *************************************************************************/
    
    /**
     * Make sure the constructor properly creates the DB URL. 
     * 
     * Also tests getUrl()
     * 
     * @dataProvider validDbUrlProvider
     *
     */
    public function testConstructorCreateUrl($expectedUrl, $dbObj)
    {
        $resultUrl = $dbObj->getUrl(); 
        $this->assertEquals($expectedUrl, $resultUrl);
    }
    
    /**
     * Make sure the constructor fails with an exception with invalid db names
     * 
     * @expectedException Sopha_Exception
     * @dataProvider      badDbNameProvider
     */
    public function testConstructorInvalidDbNames($db)
    {
        new Sopha_Db($db);
    }
    
    /**
     * Make sure the constructor fails if an invalid hostname is provided
     * 
     * @expectedException Sopha_Exception
     * @dataProvider      badHostNameProvider
     */
    public function testConstructorInvalidHostNames($host)
    {
        new Sopha_Db('mydb', $host);
    }
    
    /**
     * Make sure the constructor fails if an invalid port is provided
     *
     * @expectedException Sopha_Exception
     * @dataProvider      badPortProvider
     */
    public function testConstructorInvalidPorts($port)
    {
        new Sopha_Db('mydb', 'localhost', $port);
    }
    
    /**
     * Test that we can properly create a DB
     *
     */
    public function testCreateDb()
    {
        if (! $this->_url) $this->markTestSkipped("Test requires a CouchDb server set up - see TestConfiguration.php");
        
        list($host, $port, $dbname) = $this->_getUrlParts();
        $dbname = trim($dbname, '/');
        
        $db = Sopha_Db::createDb($dbname, $host, $port);
        
        // Make sure DB now exists
        $response = Sopha_Http_Request::get($this->_url);
        $this->assertEquals(200, $response->getStatus());
    }
    
    /**
     * Make sure we get an exception when trying to create an existing DB 
     * 
     */
    public function testCreateExistingDbFails()
    {
        if (! $this->_url) $this->markTestSkipped("Test requires a CouchDb server set up - see TestConfiguration.php");
        
        list($host, $port, $dbname) = $this->_getUrlParts();
        $dbname = trim($dbname, '/');
        
        try {
            $db = Sopha_Db::createDb($dbname, $host, $port);
            $this->fail("::createDb was expected to fail with a 409 error code");
        } catch (Sopha_Db_Exception $e) {
            $this->assertEquals(409, $e->getCode(), "Error code is not 409");
        }
    }
    
    /**
     * Test that we can properly delete a 
     */
    public function testDeleteDb()
    {
        if (! $this->_url) $this->markTestSkipped("Test requires a CouchDb server set up - see TestConfiguration.php");
        
        list($host, $port, $dbname) = $this->_getUrlParts();
        $dbname = trim($dbname, '/');
        
        $res = Sopha_Db::deleteDb($dbname, $host, $port);
        $this->assertTrue($res);
        
        // Make sure the DB no longer exists
        $response = Sopha_Http_Request::get($this->_url);
        $this->assertEquals(404, $response->getStatus());
    }

    /**
     * Test that we can get a list of all DBs from the server
     */
    public function testGetAllDbs()
    {
        if (! $this->_url) $this->markTestSkipped("Test requires a CouchDb server set up - see TestConfiguration.php");
        
        list($host, $port, $dbname) = $this->_getUrlParts();
        $dbname = trim($dbname, '/');
        
        // First, create a DB
        Sopha_Db::createDb($dbname, $host, $port);
        
        // Make sure DB exists by looking for it in the list of all DBs
        $dbs = Sopha_Db::getAllDbs($host, $port);
        $this->assertType('array', $dbs);
        $this->assertTrue(in_array($dbname, $dbs));
        
        // Delete the DB and make sure it is no longer in the list
        Sopha_Db::deleteDb($dbname, $host, $port);
        $dbs = Sopha_Db::getAllDbs($host, $port);
        $this->assertType('array', $dbs);
        $this->assertFalse(in_array($dbname, $dbs));
    }

    /*************************************************************************
     * Dynamic (object) tests
     *************************************************************************/
    
    /**
     * Test that we can create a document without a URL
     */
    public function testCreateAutoGeneratedId()
    {
        if (! $this->_url) $this->markTestSkipped("Test requires a CouchDb server set up - see TestConfiguration.php");
        $db = $this->_setupDb();
        
        $doc1 = $db->create(array('foo' => 'bar'));
        $doc2 = $db->create(array('foo' => 'baz'));
        
        $this->assertType('Sopha_Document', $doc1);
        $this->assertType('Sopha_Document', $doc2);
        $this->assertNotEquals($doc1->getId(), $doc2->getId());

        $this->_teardownDb();
    }
    
    /**
     * Test that we can create a document with a preset URL
     *
     */
    public function testCreatePresetUrl()
    {
        if (! $this->_url) $this->markTestSkipped("Test requires a CouchDb server set up - see TestConfiguration.php");
        $db = $this->_setupDb();
        
        $doc = $db->create(array('a' => 1), 'mydoc');
        $this->assertEquals('mydoc', $doc->getId());
        
        $this->_teardownDb();
    }
    
    /**
     * Test that an exception is thrown if tryign to create the same doc twice
     *
     */
    public function testCreateSameDocException()
    {
        if (! $this->_url) $this->markTestSkipped("Test requires a CouchDb server set up - see TestConfiguration.php");
        $db = $this->_setupDb();
        
        $doc = $db->create(array('a' => 1), 'mydoc');
        
        
        try { 
            $doc = $db->create(array('a' => 1), 'mydoc');
            $this->_teardownDb();
            $this->fail("Expected Sopha_Db_Exception was not thrown");
        } catch (Sopha_Db_Exception $e) {
            $this->_teardownDb();
            $this->assertEquals(412, $e->getCode(), "HTTP error code is expected to be 412");
        }
    }

    /**
     * Make sure a document exists in DB and can be retrieved 
     * 
     */
    public function testRetrieve()
    {
        if (! $this->_url) $this->markTestSkipped("Test requires a CouchDb server set up - see TestConfiguration.php");
        $db = $this->_setupDb();
        
        $id = time();
        $value = rand(1, 1000);
        $db->create(array('mtime' => $value), $id);
        
        // Try to fetch the document
        $doc = $db->retrieve($id);
        $this->assertType('Sopha_Document', $doc);
        $this->assertEquals($value, $doc->mtime);
        
        $this->_teardownDb();
    }
    
    /**
     * Make sure retrieve() returns boolean false if document does not exist 
     * 
     */
    public function testRetrieveReturnFalseIfNotFound()
    {
        if (! $this->_url) $this->markTestSkipped("Test requires a CouchDb server set up - see TestConfiguration.php");
        $db = $this->_setupDb();

        $ret = $db->retrieve('nonexistingdoc');
        $this->assertFalse($ret);
        
        $this->_teardownDb();
    }
    
    /**
     * Test that we can delete an existing document from DB
     */
    public function testDelete()
    {
        if (! $this->_url) $this->markTestSkipped("Test requires a CouchDb server set up - see TestConfiguration.php");
        $db = $this->_setupDb();
        
        $db->create(array('a' => 1), 'mydoc');
        
        // Make sure document exists in DB
        $doc = $db->retrieve('mydoc');
        $this->assertType('Sopha_Document', $doc);
        
        // Delete document
        $ret = $db->delete('mydoc', $doc->getRevision());
        
        // Make sure return value is true
        $this->assertTrue($ret);
        
        // Try to fetch doc again 
        $this->assertFalse($db->retrieve('mydoc'));
        
        $this->_teardownDb();
    }
    
    /**
     * Test that deleting a missing document returns false
     *
     * @expectedException Sopha_Db_Exception
     */
    public function testDeleteMissingDocument()
    {
        if (! $this->_url) $this->markTestSkipped("Test requires a CouchDb server set up - see TestConfiguration.php");
        $db = $this->_setupDb();
        
        // Delete missing document
        $ret = $db->delete('mydoc', 12345);
        
        $this->_teardownDb();
    }
    
    /**
     * Tests Sopha_Db->update()
     */
    public function testUpdateInvalidInput()
    {
        // TODO Auto-generated Sopha_DbTest->testUpdate()
        $this->markTestIncomplete("update test not implemented");
        $this->Sopha_Db->update(/* parameters */);
    }
    
    public function testUpdateObject()
    {
        $this->markTestIncomplete();
    }
    
    public function testUpdateArray()
    {
        $this->markTestIncomplete();
    }
    
    public function testUpdateObjectUrl()
    {
        $this->markTestIncomplete();
    }
    
    public function testUpdateArrayUrl()
    {
        $this->markTestIncomplete();
    }
   
    public function testUpdateArrayMissingUrl()
    {
        $this->markTestIncomplete();
    }
    
    public function testUpdateArrayMissingRevision()
    {
        $this->markTestIncomplete();
    }
    
    public function testUpdateRevisionConflict()
    {
        $this->markTestIncomplete();
    }
    
    /**
     * Tests Sopha_Db->getInfo()
     */
    public function testGetInfo()
    {
        // TODO Auto-generated Sopha_DbTest->testGetInfo()
        $this->markTestIncomplete("getInfo test not implemented");
        $this->Sopha_Db->getInfo(/* parameters */);
    }

    /**
     * Tests Sopha_Db->view()
     */
    public function testView()
    {
        // TODO Auto-generated Sopha_DbTest->testView()
        $this->markTestIncomplete("view test not implemented");
        $this->Sopha_Db->view(/* parameters */);
    }
    
    public function testAdHocView()
    {
        if (! $this->_url) $this->markTestSkipped("Test requires a CouchDb server set up - see TestConfiguration.php");
        $db = $this->_setupDb();
        
        // Create several documents
        $db->create(array('doctype' => 'text', 'name' => 'a', 'value' => 4));
        $db->create(array('doctype' => 'text', 'name' => 'b', 'value' => 3));
        $db->create(array('doctype' => 'text', 'name' => 'c', 'value' => 2));
        $db->create(array('doctype' => 'text', 'name' => 'd', 'value' => 1));
        $db->create(array('doctype' => 'image', 'name' => 'a', 'value' => 4));
        $db->create(array('doctype' => 'image', 'name' => 'b', 'value' => 3));
        $db->create(array('doctype' => 'image', 'name' => 'c', 'value' => 2));
        $db->create(array('doctype' => 'image', 'name' => 'd', 'value' => 1));
        
        // Call ad-hoc view to only fetch text docs ordered by value field
        $tempView = array(
            'map' => "function(doc) { if (doc.doctype == 'text') { emit(doc.value, doc); } } "
        );
        $result = $db->view($tempView); /* @var $result Sopha_View_Result */ 
        
        $this->assertType('Sopha_View_Result', $result);
        $this->assertEquals(4, $result->total_rows);
        $this->assertEquals('d', $result[0]['name']);
        
        $this->_teardownDb();
    }
    
    /*************************************************************************
     * Data Providers
     ************************************************************************/
	
	/**
     * Provide test data for testConstructorCreateUrl
     *
     * @see ::testConstructorCreateUrl()
     */
    public static function validDbUrlProvider()
    {
        return array(
        	array('http://localhost:5984/mydb/', 
                new Sopha_Db('mydb')),
            array('http://couchserver:5984/mydb/',
                new Sopha_Db('mydb', 'couchserver')),
            array('http://couchserver:591/mydb/', 
                new Sopha_Db('mydb', 'couchserver', 591)),
            array('http://couch.example.net:13/fu%2Fgu/',
                new Sopha_Db('fu/gu', 'couch.example.net', 13)),
            array('http://1.2.3.4:10001/db_$()%2F+-x/',
                new Sopha_Db('db_$()/+-x', '1.2.3.4', 10001))
        );
    }    

    /**
     * Data provider for testConstructorInvalidDbNames
     *
     * @see    ::testConstructorInvalidDbNames()
     * @return array
     */
    public static function badDbNameProvider()
    {
        return array(
            array('0digit'),
            array('UpperCase'),
            array('moreUpperCase'),
            array('in!valid'),
            array('has space'),
            array('_underscore'),
            array('in:valid'),
            array('in?valid')
        );
    }
    
    /**
     * Data provider for testConstructorInvalidHostNames
     *
     * @see    ::testConstructorInvalidHostNames()
     * @return array
     */
    public static function badHostNameProvider()
    {
        return array(
			array(''),
            array('local host'),
            array('local_host'),
            array('foo%bar'),
            array('baz@baz.com'),
            array('שטוייעס.com'),
            array('--dot.com'),
            array('local/host'),
            array('http://')        
        );
    }

    /**
     * Data provider for testConstructorInvalidPorts
     *
     * @see    ::testConstructorInvalidPorts()
     * @return array
     */
    public static function badPortProvider()
    {
        return array(
            array(0),
            array(-12),
            array(0x10000),
            array(':55'),
            array('string'),
        );
    }

    /*************************************************************************
     * Helper Methods
     ************************************************************************/
    
    /**
     * Get the host, port and path of a provided URL
     *
     * @param  string $url
     * @return array
     */
    protected function _getUrlParts($url = null)
    {
        if (! $url) $url = $this->_url;
        $parts = parse_url($url);
        
        return array($parts['host'], $parts['port'], $parts['path']);
    }
    
    /**
     * Create a clean test DB before a test and return it 
     *
     * @return Sopha_Db
     */
    protected function _setupDb()
    {
        // First, delete the DB if it exists
        $this->_teardownDb();
        
        // Then, set up a clean new DB and return it
        list ($host, $port, $db) = $this->_getUrlParts();
        $db = trim($db, '/');
        
        return Sopha_Db::createDb($db, $host, $port);
    }
    
    /**
     * Destroy an existing DB after a test
     *
     */
    protected function _teardownDb()
    {
        list ($host, $port, $db) = $this->_getUrlParts();
        $db = trim($db, '/');
        
        try {
            Sopha_Db::deleteDb($db, $host, $port);
        } catch (Sopha_Db_Exception $e) {
            if ($e->getCode() != 404) {
                throw $e;
            }
        }
    }
}
