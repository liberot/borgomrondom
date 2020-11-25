<?php

$d = file_get_contents('./typeform_survey.json');
$d = json_decode($d);
$d = json_encode($d, JSON_PRETTY_PRINT);

print_r($d);


