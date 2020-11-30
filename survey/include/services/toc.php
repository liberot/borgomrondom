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

/***************************
add_action('admin_post_exec_save_toc', 'exec_save_toc');
function exec_save_toc(){

// policy
     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

// session client
     $author_id = get_author_id();

// tickets
     $thread_id = trim_incoming_numeric($_POST['thread_id']);
     $thread_id = get_session_ticket('thread_id');

     $section_id = trim_incoming_numeric($_POST['section_id']);
     $section_id = get_session_ticket('section_id');

// the section
     $section = get_section_by_id($section_id)[0];
     if(is_null($section)){
          $message = esc_html(__('no such section', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     $section->post_content = pagpick($section->post_content);

     $panel_ref = trim_incoming_numeric($_POST['panel_ref']);
     $panel_ref = get_session_ticket('panel_ref');

     $history = trim_incoming_toc($_POST['history']);
     $book = trim_incoming_toc($_POST['book']);

     $section->post_content['toc']['history'] = $history;
     $section->post_content['toc']['book'] = $book;
     $section->post_content = pigpack($section->post_content);
     $section->post_author = $author_id;

     $section_id = wp_insert_post($section);

     $coll = get_section_by_id($section_id);

     $message = esc_html(__('toc is saved', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}
*/

add_action('admin_post_exec_save_thread', 'exec_save_thread');
function exec_save_thread(){

// policy
     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

// session client
     $author_id = get_author_id();

// tickets
     $thread_id = trim_incoming_numeric($_POST['thread_id']);
     $thread_id = get_session_ticket('thread_id');

     $book = trim_incoming_toc($_POST['book']);
     $history = trim_incoming_toc($_POST['history']);

     $conditions = trim_incoming_key_val($_POST['conditions']);

     $thread = get_thread_by_id($thread_id)[0];
     if(is_null($thread)){
          $message = esc_html(__('no such thread', 'nosuch'));
          echo json_encode(array('res'=>'success', 'message'=>$message));
          return false;
     }

     $thread->post_content = pagpick($thread->post_content);

     $thread->post_content['book'] = null == $book ? [] : $book;
     $thread->post_content['history'] = null == $history ? [] : $history;
     $thread->post_content['conditions'] = null == $conditions ? [] : $conditions;
     $thread->post_author = $author_id;
     $thread->post_content = pigpack($thread->post_content);

     $thread_id = wp_insert_post($thread);

     $coll = get_thread_by_id($thread_id);

     $message = esc_html(__('thread is saved', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}

