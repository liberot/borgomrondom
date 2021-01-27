<?php if(true != ('cli' == php_sapi_name())){ exit(); }



/***
 php -d memory_limit=-1 ./include/cli/craw_typeform_survey.php



*/

$cutt = '/\/wp-content\/plugins\/bookbuilder\/survey\/include\/cli/';
$base = preg_replace($cutt, '', __DIR__);
$path = sprintf('%s%s', $base, '/wp-load.php');

require_once($path);

$survey_title = 'BBC0-Cover-and-Prefa--FvSIczF7.json';

$res = crawl_typeform_survey($survey_title);
print "Table Of Contents of the Questionnaire: ";
print $survey_title;
print PHP_EOL;
print_r($res);
print PHP_EOL;
print PHP_EOL;
print PHP_EOL;

$res = flatten_toc_refs($res);
print "Walkthrough of thie Fields of the Questionnaire: ";
print $survey_title;
print PHP_EOL;
print_r($res);
print PHP_EOL;
print PHP_EOL;
print PHP_EOL;

print PHP_EOL;
print_r('done');
print PHP_EOL;

exit();

