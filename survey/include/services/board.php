<?php defined('ABSPATH') || exit;

add_action('admin_post_exec_get_panels_of_client', 'exec_get_panels_of_client');
function exec_get_panels_of_client(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $coll = [];
     $res = get_threads_of_client();
     foreach($res as $thread){
          $coll[]= get_panels_by_thread_id($thread->ID);
     }
     
     $message = esc_html(__('panels is loaded', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}

add_action('admin_post_exec_get_panels_by_thread_id', 'exec_get_panels_by_thread_id');
function exec_get_panels_by_thread_id(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $thread_id = trim_incoming_numeric($_POST['thread_id']);
     $thread_id = get_session_ticket('thread_id');

     $coll = get_panels_by_thread_id($thread_id);
     $message = esc_html(__('panels is loaded', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}

add_action('admin_post_exec_init_panel', 'exec_init_panel');
function exec_init_panel(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $author_id = get_current_user_id();
     $panel_id = trim_incoming_string($_POST['panel_id']);

     $thread_id = $_POST['thread_id'];
     $thread_id = get_session_ticket('thread_id');

     $ref = trim_incoming_string($_POST['ref']);

     $coll = [];

// the id updates the existing panel
     $conf = [
//        'ID'=>$panel_id,
          'post_author'=>$author_id,
          'post_type'=>'surveyprint_panel',
          'post_parent'=>$thread_id,
          'post_excerpt'=>$ref,
          'post_content'=>pigpack(walk_the_doc($_POST['panel']))
     ];
     $panel_id = init_panel($conf);

     $message = esc_html(__('panel saved', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$panel_id));
}

add_action('admin_post_exec_get_panel_by_ref', 'exec_get_panel_by_ref');
function exec_get_panel_by_ref(){
     if(!policy_match([Role::ADMIN, Role::CUSTOMER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $section_id = trim_incoming_filename($_POST['section_id']);
     $section_id = get_session_ticket('section_id');

     $panel_ref = trim_incoming_filename($_POST['panel_ref']);
     $coll = get_panel_by_ref($section_id, $panel_ref);

     $message = esc_html(__('panel is loaded', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}

add_action('exec_test', 'test');
function test(){
    print_r($_POST);
}

