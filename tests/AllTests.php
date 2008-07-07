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

require_once dirname(__FILE__) . '/TestHelper.php';

require_once 'Sopha/Http/RequestTest.php';
require_once 'Sopha/DbTest.php';

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
	$this->addTestSuite('Sopha_DbTest');
    }
    
    /**
     * Creates the suite.
     */
    public static function suite ()
    {
        return new self();
    }
}

