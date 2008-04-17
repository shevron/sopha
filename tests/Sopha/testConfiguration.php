<?php

/**
 * Sopha library - Unit test configuration file
 *
 * @package  Sopha 
 * @category tests
 * @author   Shahar Evron
 * @version  $Id: Db.php 4 2008-03-28 18:30:35Z shahar.e $
 * @license  LICENSE.txt - New BSD License 
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

