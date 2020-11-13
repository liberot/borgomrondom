<?php defined('ABSPATH') || exit;

add_action('admin_post_exec_get_toc_by_id', 'exec_get_toc_by_id');
function exec_get_toc_by_id(){
     if(!policy_match([Role::ADMIN, Role::CUSTOMER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     $toc_id = trim_incoming_numeric($_POST['toc_id']);
     $coll = get_toc_by_id($toc_id);
     $message = esc_html(__('toc is loaded', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}

add_action('admin_post_exec_save_toc', 'exec_save_toc');
function exec_save_toc(){
     if(!policy_match([Role::ADMIN, Role::CUSTOMER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     $author_id = get_author_id();

     $thread_id = trim_incoming_numeric($_POST['thread_id']);
     $thread_id = get_session_var('thread_id');

     $toc = walk_the_doc($_POST['toc']);
     $toc = pigpack($toc);
     $conf = [
          'post_type'=>'surveyprint_toc',
          'post_author'=>$author_id,
          'post_content'=>$toc,
          'post_parent'=>$thread_id
     ];
     $toc_id = save_toc($conf);

     $coll = [];
     $coll['toc'] = get_toc_by_id($toc_id);
     $message = esc_html(__('toc is saved', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}


