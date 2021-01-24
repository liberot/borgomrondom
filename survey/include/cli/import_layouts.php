<?php if(true != ('cli' == php_sapi_name())){ exit(); }

/***
 php -d memory_limit=-1 ./include/cli/import_layouts.php


*/

$cutt = '/\/wp-content\/plugins\/bookbuilder\/survey\/include\/cli/';
$base = preg_replace($cutt, '', __DIR__);
$path = sprintf('%s%s', $base, '/wp-load.php');

require_once($path);







$res = import_layouts();
print_r($res);







print PHP_EOL;
print_r('bye');
print PHP_EOL;

exit();

