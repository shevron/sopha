<?php

/**
 * Sopha library - All Tests test suite
 *
 * @package  Sopha 
 * @category tests
 * @author   Shahar Evron
 * @version  $Id: Db.php 4 2008-03-28 18:30:35Z shahar.e $
 * @license  LICENSE.txt - New BSD License 
 */

require_once 'testConfiguration.php';

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'Http/RequestTest.php';

/**
 * Static test suite.
 */
class AllTests extends PHPUnit_Framework_TestSuite
{
    /**
     * Constructs the test suite handler.
     */
    public function __construct ()
    {
        $this->setName('AllTests');
        $this->addTestSuite('Sopha_Http_RequestTest');
    }
    /**
     * Creates the suite.
     */
    public static function suite ()
    {
        return new self();
    }
}

