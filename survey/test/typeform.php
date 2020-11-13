<?php defined('ABSPATH') || exit;

add_action('init', 'exec_typeform_test');
function exec_typeform_test(){

     // $res = exec_download_typeform_survey();
     $res = exec_construct_typeform_survey();

}
