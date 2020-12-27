<?php if(true != ('cli' == php_sapi_name())){ exit(); }

$cutt = '/\/wp-content\/plugins\/bookbuilder\/survey\/include\/cli/';
$base = preg_replace($cutt, '', __DIR__);
$path = sprintf('%s%s', $base, '/wp-load.php');

require_once($path);

$res = init_log('cli-test', []);
print_r($res);
print PHP_EOL;

print_r(add_base_to_chunk(base64_encode(' t e s t ')));
print PHP_EOL;

print_r('bye');
print PHP_EOL;

exit();

