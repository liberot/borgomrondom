<?php defined('ABSPATH') || exit;

add_action('admin_post_exec_get_toc_by_id', 'exec_get_toc_by_id');
function exec_get_toc_by_id(){
     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
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

     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     $author_id = get_author_id();

     $thread_id = trim_incoming_numeric($_POST['thread_id']);
     $thread_id = get_session_ticket('thread_id');

     $section_id = trim_incoming_numeric($_POST['section_id']);
     $section_id = get_session_ticket('section_id');

     $panel_ref = trim_incoming_numeric($_POST['panel_ref']);
     $panel_ref = get_session_ticket('panel_ref');

     $toc = get_toc_by_thread_id($thread_id)[0];
     if(is_null($toc)){
          $message = esc_html(__('no toc', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message, 'coll'=>$coll));
          return false;
     }

// todo trim arrays
     $history = $_POST['history'];
     $booktoc = $_POST['booktoc'];
// 

     $toc->post_content = pagpick($toc->post_content);
     $toc->post_content['history'] = $history;
     $toc->post_content['booktoc'] = $booktoc;
     $toc->post_content = pigpack($toc->post_content);

     $conf = [
          'post_type'=>'surveyprint_toc',
          'post_author'=>$author_id,
          'post_content'=>$toc->post_content,
          'post_parent'=>$thread_id
     ];

     if(Server::UPDATE_ON_PERSIST){
          $conf['ID'] = $toc->ID;
     }

     $toc_id = save_toc($conf);

     $coll = [];
     $coll['toc'] = get_toc_by_id($toc_id);
     $message = esc_html(__('toc is saved', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}


