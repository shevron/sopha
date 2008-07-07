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

/**
 * Test Helper File
 * 
 * This file should be included by all tests and will ensure that tests can be
 * executed by setting up the test environment.
 */

// Include PHPUnit dependencies
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Framework/IncompleteTestError.php';
require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/Runner/Version.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
require_once 'PHPUnit/Util/Filter.php';

// Set error reporting to the level to which Zend Framework code must comply.
error_reporting( E_ALL | E_STRICT ); 

// Determine paths
$_path_root  = realpath(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR; 
$_path_lib   = $_path_root . 'library';
$_path_tests = $_path_root . 'tests';


// Omit the contents of the tests directory from code coverage reports
PHPUnit_Util_Filter::addDirectoryToFilter($_path_tests, '.php');

// Set the include path to only include library and test dirs
set_include_path($_path_lib . PATH_SEPARATOR . $_path_tests);

// Read the configuration file if available
if (is_readable($_path_tests . DIRECTORY_SEPARATOR . 'TestConfiguration.php')) {
    require_once $_path_tests . DIRECTORY_SEPARATOR . 'TestConfiguration.php';
} else {
    require_once $_path_tests . DIRECTORY_SEPARATOR . 'TestConfiguration.php.dist';
}

// Add the Sopha /library directory to the PHPUnit code coverage whitelist 
if (defined('TESTS_GENERATE_REPORT') && TESTS_GENERATE_REPORT === true && 
    version_compare(PHPUnit_Runner_Version::id(), '3.1.6', '>=')) {
    PHPUnit_Util_Filter::addDirectoryToWhitelist($_path_lib);
}

// Unset global variables that are no longer needed
unset($_path_root, $_path_lib, $_path_tests);

