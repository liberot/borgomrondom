<?php

$abs = (dirname(__FILE__));
$abs = str_replace('/wp-content/plugins/bookbuilder/survey/include/services', '', $abs);

require_once(sprintf('%s%s', $abs, '/wp-load.php'));
require_once(sprintf('%s%s', $abs, '/wp-admin/includes/admin.php'));
nocache_headers();

do_action('admin_init');

$action = empty( $_REQUEST['action'] ) ? '' : $_REQUEST['action'];

if(!is_user_logged_in()){
     if(empty($action)){
          do_action('admin_post_nopriv');
     } 
     else{
          do_action("admin_post_nopriv_{$action}");
     }
} 
else {
     if(empty($action)){
          do_action('admin_post');
     } 
     else{
          do_action("admin_post_{$action}");
     }
}
