<?php

/**
 * Sopha CouchDB View Server
 * 
 * Hook this view server into CouchDB by adding the following line to CouchDB's
 * local.ini file:
 * 
 * [query_servers]
 * php=/path/to/php /path/to/this/file
 * 
 */

// Add the Sopha library to include path if it is available
$lib_path = realpath(dirname(dirname(__FILE__)) . '/library/Sopha');
if (is_dir($lib_path)) {
    set_include_path(dirname($lib_path) . PATH_SEPARATOR . get_include_path());
}

require_once 'Sopha/ViewServer.php';

$server = new Sopha_ViewServer();

$server->run();

