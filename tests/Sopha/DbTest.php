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

// Load the test helper
require_once dirname(__FILE__) . '/../../TestHelper.php';

require_once 'Sopha/Db.php';

/**
 * Sopha_Db test case
 * 
 */
class Sopha_DbTest extends PHPUnit_Framework_TestCase
{
    /**
     * Db object
     * 
     * @var Sopha_Db
     */
    protected $db;
    
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
    protected function setUp ()
    {
        parent::setUp();
        
        if (defined('SOPHA_TEST_DB_URL') && SOPHA_TEST_DB_URL) {
            $this->_url = SOPHA_TEST_DB_URL;
        } else {
            $this->_url = null;
        }
    }
    
    /**
     * Tests Sopha_Db->__construct()
     */
    public function test__construct ()
    {
        // TODO Auto-generated Sopha_DbTest->test__construct()
        $this->markTestIncomplete("__construct test not implemented");
        $this->Sopha_Db->__construct(/* parameters */);
    }
    
    /**
     * Tests Sopha_Db->create()
     */
    public function testCreate ()
    {
        // TODO Auto-generated Sopha_DbTest->testCreate()
        $this->markTestIncomplete("create test not implemented");
        $this->Sopha_Db->create(/* parameters */);
    }
    
    /**
     * Tests Sopha_Db::createDb()
     */
    public function testCreateDb ()
    {
        // TODO Auto-generated Sopha_DbTest::testCreateDb()
        $this->markTestIncomplete("createDb test not implemented");
        Sopha_Db::createDb(/* parameters */);
    }
    
    /**
     * Tests Sopha_Db->delete()
     */
    public function testDelete ()
    {
        // TODO Auto-generated Sopha_DbTest->testDelete()
        $this->markTestIncomplete("delete test not implemented");
        $this->Sopha_Db->delete(/* parameters */);
    }
    
    /**
     * Tests Sopha_Db::deleteDb()
     */
    public function testDeleteDb ()
    {
        // TODO Auto-generated Sopha_DbTest::testDeleteDb()
        $this->markTestIncomplete("deleteDb test not implemented");
        Sopha_Db::deleteDb(/* parameters */);
    }
    
    /**
     * Tests Sopha_Db::getAllDbs()
     */
    public function testGetAllDbs ()
    {
        // TODO Auto-generated Sopha_DbTest::testGetAllDbs()
        $this->markTestIncomplete("getAllDbs test not implemented");
        Sopha_Db::getAllDbs(/* parameters */);
    }
    
    /**
     * Tests Sopha_Db->getAllDocs()
     */
    public function testGetAllDocs ()
    {
        // TODO Auto-generated Sopha_DbTest->testGetAllDocs()
        $this->markTestIncomplete("getAllDocs test not implemented");
        $this->Sopha_Db->getAllDocs(/* parameters */);
    }
    
    /**
     * Tests Sopha_Db->getInfo()
     */
    public function testGetInfo ()
    {
        // TODO Auto-generated Sopha_DbTest->testGetInfo()
        $this->markTestIncomplete("getInfo test not implemented");
        $this->Sopha_Db->getInfo(/* parameters */);
    }
    
    /**
     * Tests Sopha_Db->getUrl()
     */
    public function testGetUrl ()
    {
        // TODO Auto-generated Sopha_DbTest->testGetUrl()
        $this->markTestIncomplete("getUrl test not implemented");
        $this->Sopha_Db->getUrl(/* parameters */);
    }
    
    /**
     * Tests Sopha_Db->retrieve()
     */
    public function testRetrieve ()
    {
        // TODO Auto-generated Sopha_DbTest->testRetrieve()
        $this->markTestIncomplete("retrieve test not implemented");
        $this->Sopha_Db->retrieve(/* parameters */);
    }
    
    /**
     * Tests Sopha_Db->update()
     */
    public function testUpdate ()
    {
        // TODO Auto-generated Sopha_DbTest->testUpdate()
        $this->markTestIncomplete("update test not implemented");
        $this->Sopha_Db->update(/* parameters */);
    }
    
    /**
     * Tests Sopha_Db->view()
     */
    public function testView ()
    {
        // TODO Auto-generated Sopha_DbTest->testView()
        $this->markTestIncomplete("view test not implemented");
        $this->Sopha_Db->view(/* parameters */);
    }
}
