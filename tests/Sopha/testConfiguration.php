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

/**
 * Test Configuration File 
 * 
 * Edit this file if you need to before running any tests
 */

// Set the include path
define('SOPHA_LIB_PATH', realpath(dirname(dirname(dirname(__FILE__))) . 
                         DIRECTORY_SEPARATOR . 'library'));
                         
set_include_path(SOPHA_LIB_PATH . PATH_SEPARATOR . get_include_path());


/**
 * Testing configuration
 */

/**
 * CouchDB server location
 * 
 * Set ST_TESTDB_URL to boolean false if you don't havea CouchDB server set up and
 * want to skip these tests
 */

define('ST_TESTDB_URL',   'http://localhost:5984/sopha__test__');
