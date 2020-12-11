<?php defined('ABSPATH') || exit;



/***
   evaluates the threads of a client
 */
add_action('admin_post_exec_get_threads_of_client', 'exec_get_threads_of_client');
function exec_get_threads_of_client(){
     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     $coll = get_threads_of_client();
     $message = esc_html(__('threads is loaded', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}



/***
   sets up a thread
 */
add_action('admin_post_exec_init_thread', 'exec_init_thread');
function exec_init_thread(){

// policy
     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

// this might change
// read of threads and sections of a client
// todo:: client might own on or more threads
     $threads = get_threads_of_client();
     if(!empty($threads)){
          $coll['thread'] = $threads;
          $coll['section'] = get_sections_by_thread_id($coll['thread'][0]->ID);
          set_session_ticket('thread_id', $coll['thread'][0]->ID, true);
          set_session_ticket('section_id', $coll['section'][0]->ID, true);
          $message = esc_html(__('stored thread is loaded', 'nosuch'));
          echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
          return true;
     }

// no thread so far
// load of the survey 'Cover and Preface'
     $survey = get_survey_by_title('201204 Cover and Preface')[0];
     if(is_null($survey)){
          $message = esc_html(__('no cover and preface survey', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     $survey_id = $survey->ID;

// init of a customer thread
     $author_id = get_author_id();
     $surveyprint_uuid = psuuid();

// insert of a post of type thread
     $toc = [];
     $toc['book'] = [];
     $toc['history'] = [];
     $toc['conditions'] = [];
     $toc = pigpack($toc);
     $conf = [
          'post_type'=>'surveyprint_thread',
          'post_author'=>$author_id,
          'post_title'=>$surveyprint_uuid,
          'post_excerpt'=>$surveyprint_uuid,
          'post_name'=>$surveyprint_uuid,
          'post_content'=>$toc
     ];
     $thread_id = init_thread($conf);
     if(is_null($thread_id)){
          $message = esc_html(__('could not init a thread', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

// init of a section
     $section_id = init_section_from_survey($thread_id, $survey->post_excerpt);
     if(is_null($section_id)){
          $message = esc_html(__('no section', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

// generation of the panels of the section
     $coll = [];
     $coll['panels'] = init_panels_from_survey($section_id, $survey_id);

// result 
// session ticket
     set_session_ticket('thread_id', $thread_id, true);
     set_session_ticket('section_id', $section_id, true);

     $coll['thread'] = get_thread_by_id($thread_id);
     $coll['section'] = get_sections_by_thread_id($thread_id);

     $message = esc_html(__('thread inited', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
     return true;
}

add_action('admin_post_exec_get_section_by_id', 'exec_get_section_by_id');
function exec_get_section_by_id(){
// policy
     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
// result
     $section_id = trim_incoming_filename($_POST['section_id']);
     $coll = get_section_by_id($section_id);
     $message = esc_html(__('section loaded', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}

add_action('admin_post_exec_get_section_by_ref', 'exec_get_section_by_ref');
function exec_get_section_by_ref(){

// policy
     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $thread_id = get_session_ticket('thread_id');
     $section_ref = trim_incoming_filename($_POST['section_ref']);

// read of the requested section
     $section = get_section_by_ref($thread_id, $section_ref)[0];

     if(!is_null($section)){
          $coll = [];
          $coll['section'] = $section;
          set_session_ticket('section_id', $section->ID, true);
          $message = esc_html(__('section loaded', 'nosuch'));
          echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
          return true;
     }

// no section - init of the requested section
     $survey = get_survey_by_ref($section_ref)[0];
     if(is_null($survey)){
          $message = esc_html(__('no survey', 'nosuch'));
          echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
          return false;
     }

     $section_id = init_section_from_survey($thread_id, $survey->post_excerpt);
     if(is_null($section_id)){
          $message = esc_html(__('could not init section', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

// result
     set_session_ticket('section_id', $section_id, true);
     $coll = [];
     $coll['section'] = get_section_by_ref($thread_id, $section_ref)[0];
     $message = esc_html(__('section loaded', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}

