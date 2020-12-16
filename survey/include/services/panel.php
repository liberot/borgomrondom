<?php defined('ABSPATH') || exit;

add_action('admin_post_exec_get_panels_of_client', 'exec_get_panels_of_client');
function exec_get_panels_of_client(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

// todo:: client probably has more threads
     $coll = [];
     $thread = get_thread_of_client()[0];
     if(is_null($thread)){
          $message = esc_html(__('client has no thread', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     $coll[]= get_panels_by_thread_id($thread->ID);
     
     $message = esc_html(__('panels is loaded', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}

add_action('admin_post_exec_get_panels_by_thread_id', 'exec_get_panels_by_thread_id');
function exec_get_panels_by_thread_id(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
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

     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $author_id = get_author_id();

     $panel_ref = trim_incoming_filename($_POST['panel_ref']);
     $panel_ref = get_session_ticket('panel_ref');

     if(is_null($panel_ref)){
          $message = esc_html(__('panel corrupt', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $thread_id = trim_incoming_filename($_POST['thread_id']);
     $thread_id = get_session_ticket('thread_id');
     if(is_null($thread_id)){
          $message = esc_html(__('thread corrupt', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $section_id = $_POST['section_id'];
     $section_id = get_session_ticket('section_id');
     if(is_null($section_id)){
          $message = esc_html(__('section corrupt', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $question = trim_incoming_string($_POST['question']);
     $question = trim_for_print($question);

     $answer = trim_incoming_string($_POST['answer']);
     $answer = trim_for_print($answer);

     $panel = get_panel_by_ref($section_id, $panel_ref)[0];
     if(is_null($panel)){
          $message = esc_html(__('no such panel', 'nosuch'));
          echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$panel_ref));
          return false;
     }

     $doc = pagpick($panel->post_content);
     $doc['question'] = $question;
     $doc['answer'] = $answer;
     $doc = pigpack($doc);

     $conf = [
          'post_author'=>$author_id,
          'post_type'=>'surveyprint_panel',
          'post_parent'=>$section_id,
          'post_excerpt'=>$panel_ref,
          'post_content'=>$doc
     ];

     if(Proc::UPDATE_ON_PERSIST){
          $panel = get_panel_by_ref($section_id, $panel_ref)[0];
          if(is_null($panel)){
               $message = esc_html(__('no such panel', 'nosuch'));
               echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$panel_ref));
               return false;
          }
          $conf['ID'] = $panel->ID;
     }

     $panel_id = init_panel($conf);

     $coll = get_panel_by_ref($section_id, $panel_ref);

     $message = esc_html(__('panel saved', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}



/***
   evaluates panel by reference
 */
add_action('admin_post_exec_get_panel_by_ref', 'exec_get_panel_by_ref');
function exec_get_panel_by_ref(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $thread_id = get_session_ticket('thread_id');

     $section_ref = trim_incoming_filename($_POST['section_ref']);
     if(is_null($section_ref)){
          $message = esc_html(__('section corrupt', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $panel_ref = trim_incoming_filename($_POST['panel_ref']);
     if(is_null($panel_ref)){
          $message = esc_html(__('ref corrupt', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

// todo:: init section if not there
     $section = get_section_by_ref($thread_id, $section_ref)[0];
     if(is_null($section)){
          $message = esc_html(__('no such section', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     $section_id = $section->ID;

// loads the panel
     $coll['panel'] = get_panel_by_ref($section_id, $panel_ref);

// result
     $coll['section_ref'] = $section_ref;
     $coll['panel_ref'] = $panel_ref;

// session tickets
     set_session_ticket('thread_id', $thread_id, true);
     set_session_ticket('section_id', $section_id, true);
     set_session_ticket('panel_ref', $panel_ref, true);

     $message = esc_html(__('panel is loaded', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}

add_action('exec_test', 'test');
function test(){
    print_r($_POST);
}

