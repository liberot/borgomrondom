<?php

$blblbl = dirname(__FILE__);
$lslsll = $argv[1];
if(is_null($lslsll)){
     $lslsll = '/typeform_survey.json';
}

$lslsll = preg_replace('/^\.+/', '', $lslsll);
$lslsll = preg_replace('/^\/+/', '', $lslsll);
$lslsll = sprintf('/%s', $lslsll);

$d = file_get_contents(sprintf('%s%s', $blblbl, $lslsll));
$d = json_decode($d);
$d = json_encode($d, JSON_PRETTY_PRINT);

print_r($d);


