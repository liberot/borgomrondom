<?php if(true != ('cli' == php_sapi_name())){ exit(); }

/***
 usage:
 php -d memory_limit=-1 ./include2nd/cli/import_layouts.php


*/

$cutt = '/\/wp-content\/plugins\/bookbuilder\/survey\/include2nd\/cli/';
$base = preg_replace($cutt, '', __DIR__);
$path = sprintf('%s%s', $base, '/wp-load.php');


require_once($path);



print 'about to export the svg layouts...';



$res = bb_import_layouts();
print_r($res);







print PHP_EOL;
print_r('done');
print PHP_EOL;

exit();



