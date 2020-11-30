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
     $section = get_section_by_id($section_id);
     $section->post_content = pagpick($section->post_content);

     $panel_ref = trim_incoming_numeric($_POST['panel_ref']);
     $panel_ref = get_session_ticket('panel_ref');

     $history = trim_incoming_toc($_POST['history']);
     $booktoc = trim_incoming_toc($_POST['book']);

     $section->post_content['toc']['history'] = $history;
     $section->post_content['toc']['book'] = $book;
     $section->post_content = pigpack($section->post_content);

// todo save section
//
print_r($section);

     $coll = [];
     $message = esc_html(__('toc is saved', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}


