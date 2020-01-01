<?php defined('ABSPATH') || exit;

add_action('admin_post_exec_get_toc_by_id', 'exec_get_toc_by_id');
function exec_get_toc_by_id(){
     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     $toc_id = trim_incoming_numeric($_POST['toc_id']);
     $coll = get_toc_by_id($toc_id);
     $message = esc_html(__('toc is loaded', 'bookbuilder'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}

