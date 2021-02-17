<?php

$abs = (dirname(__FILE__));
$abs = str_replace('/wp-content/plugins/bookbuilder/survey/include2nd/services', '', $abs);

require_once(sprintf('%s%s', $abs, '/wp-load.php'));
require_once(sprintf('%s%s', $abs, '/wp-admin/includes/admin.php'));
nocache_headers();

do_action('admin_init');

$action = empty( $_REQUEST['action'] ) ? '' : $_REQUEST['action'];

if(!is_user_logged_in()){
} 
else {
     if(empty($action)){
     } 
     else{
          do_action("admin_post_{$action}");
     }
}
