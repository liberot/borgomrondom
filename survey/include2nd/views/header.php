<?php defined('ABSPATH') || exit;



add_action('init', 'exec_add_headers');
function exec_add_headers(){

     header('Cache-Control: no-cache, no-store, must-revalidate');
     header('Pragma: no-cache');
     header('Expires: 0');
}



add_action('wp_head', 'exec_add_meta_tags');
function exec_add_meta_tags(){

     echo <<<EOD
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="0" />
EOD;

}




