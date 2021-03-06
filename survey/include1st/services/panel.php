<?php defined('ABSPATH') || exit;

add_action('admin_post_exec_get_panels_of_client', 'exec_get_panels_of_client');
function exec_get_panels_of_client(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     init_log('exec_get_panels_of_client', []);

// todo:: client probably has more threads
     $coll = [];
     $thread = get_thread_of_client()[0];
     if(is_null($thread)){
          $message = esc_html(__('client has no thread', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     $coll[]= get_panels_by_thread_id($thread->ID);
     
     $message = esc_html(__('panels is loaded', 'bookbuilder'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}

add_action('admin_post_exec_get_panels_by_thread_id', 'exec_get_panels_by_thread_id');
function exec_get_panels_by_thread_id(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     init_log('exec_get_panels_by_thread_id', []);

     $thread_id = trim_incoming_numeric($_POST['thread_id']);
     $thread_id = get_session_ticket('thread_id');

     $coll = get_panels_by_thread_id($thread_id);
     $message = esc_html(__('panels is loaded', 'bookbuilder'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}

add_action('admin_post_exec_init_panel', 'exec_init_panel');
function exec_init_panel(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $author_id = get_author_id();

     $panel_ref = trim_incoming_filename($_POST['panel_ref']);
     $panel_ref = get_session_ticket('panel_ref');

     if(is_null($panel_ref)){
          $message = esc_html(__('panel corrupt', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $thread_id = trim_incoming_filename($_POST['thread_id']);
     $thread_id = get_session_ticket('thread_id');
     if(is_null($thread_id)){
          $message = esc_html(__('thread corrupt', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $section_id = $_POST['section_id'];
     $section_id = get_session_ticket('section_id');
     if(is_null($section_id)){
          $message = esc_html(__('section corrupt', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     init_log('admin_post_exec_init_panel', ['thread_id'=>$thread_id, 'section_id'=>$section_id]);

     $question = trim_incoming_string($_POST['question']);
     $question = trim_for_print($question);

     $answer = trim_incoming_string($_POST['answer']);
     $answer = trim_for_print($answer);

     $panel = get_panel_by_ref($section_id, $panel_ref)[0];
     if(is_null($panel)){
          $message = esc_html(__('no such panel', 'bookbuilder'));
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
               $message = esc_html(__('no such panel', 'bookbuilder'));
               echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$panel_ref));
               return false;
          }
          $conf['ID'] = $panel->ID;
     }

     $panel_id = init_panel($conf);

     $coll = get_panel_by_ref($section_id, $panel_ref);

     $message = esc_html(__('panel saved', 'bookbuilder'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}



/***
   evaluates panel by reference
 */
add_action('admin_post_exec_get_panel_by_ref', 'exec_get_panel_by_ref');
function exec_get_panel_by_ref(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $thread_id = get_session_ticket('thread_id');

     $section_id = trim_incoming_filename($_POST['section_id']);
     if(is_null($section_id)){
          $message = esc_html(__('section corrupt', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $panel_ref = trim_incoming_filename($_POST['panel_ref']);
     if(is_null($panel_ref)){
          $message = esc_html(__('panel corrupt', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     init_log('admin_post_exec_get_panel_by_ref', ['section_id'=>$section_id, 'panel_ref'=>$panel_ref]);

// todo:: init section if not there
     $section = get_section_by_id($thread_id, $section_id)[0];
     if(is_null($section)){
          $message = esc_html(__('no such section', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

// loads the panel
     $coll['panel'] = get_panel_by_ref($section_id, $panel_ref);

// result
     $coll['section_id'] = $section_id;
     $coll['panel_ref'] = $panel_ref;

// session tickets
     set_session_ticket('thread_id', $thread_id, true);
     set_session_ticket('section_id', $section_id, true);
     set_session_ticket('panel_ref', $panel_ref, true);

     $message = esc_html(__('panel is loaded', 'bookbuilder'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}

add_action('exec_test', 'test');
function test(){
    print_r($_POST);
}

