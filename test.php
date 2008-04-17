<?php

set_include_path('./library:' . get_include_path());

require_once 'Sopha/Http/Request.php';

$res = Sopha_Http_Request::put('http://localhost:5984/sopha__test__');
echo $res;

$res = Sopha_Http_Request::put('http://localhost:5984/sopha__test2__');
echo $res;

$res = Sopha_Http_Request::delete('http://localhost:5984/sopha__test__');
echo $res;

$res = Sopha_Http_Request::delete('http://localhost:5984/sopha__test2__');
echo $res;
