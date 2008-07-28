<?php

/**
 * Sopha CouchDB Client Library - Export DB Tool
 * 
 * @todo support file attachements
 */

// Add the Sopha library to include path if it is available
$lib_path = realpath(dirname(dirname(__FILE__)) . '/library/Sopha');
if (is_dir($lib_path)) {
    set_include_path(dirname($lib_path) . PATH_SEPARATOR . get_include_path());
}

require_once 'Sopha/Db.php';

// Read command line parameters
$host = 'localhost';
$port = 5984;
$opts = getopt('d:p:h:');
if (! isset($opts['d']) || ! $opts['d']) {
    die_with_usage_error("no database specified"); 
}
$db = $opts['d'];

if (isset($opts['p']) && $opts['p']) {
    $port = $opts['p'];
}

if (isset($opts['h']) && $opts['h']) {
    $host = $opts['h'];
}

try {
    $dbo = new Sopha_Db($db, $host, $port);
    $docs = $dbo->getAllDocs();
} catch (Exception $e) {
    die_with_error(get_class($e) . ": " . $e->getMessage() . "\n", 2);
}

foreach ($docs as $docInfo) {
    $doc = $dbo->retrieve($docInfo['id']);
    echo $doc . "\n";
}

/**
 * -- END -- 
 */

/**
 * Die with an error message and a usage message and exit code 1
 *
 * @param string $message
 */
function die_with_usage_error($message)
{
    die_with_error("Error: $message\n" . get_usage(), 1);
}

/**
 * Exit the program with an error message and an exit code
 *
 * @param string  $message
 * @param integer $code
 */
function die_with_error($message, $code) 
{
    fprintf(STDERR, $message);
    exit((int) $code);
}

/**
 * Get the usage message
 *
 * @return string
 */
function get_usage()
{
    return <<<USAGE
Usage: {$_SERVER['argv'][0]} <options>
Where <options> are:
  -d <db>   Database to export (required)
  -h <host> CouchDB Hostname (defaults to 'localhost')
  -p <port> CoucgDB Port (defaults to 5984)

Examples:
  save the DB 'foo' on localhost:5984 to a gzipped file:
  # {$_SERVER['argv'][0]} -d foo | gzip > foo.couchdb.gz

USAGE;

}
