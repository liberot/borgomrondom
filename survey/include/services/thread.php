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
   sets up a new client thread
 */
add_action('admin_post_exec_init_thread', 'exec_init_thread');
function exec_init_thread(){

// policy
     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

// read of threads and sections of a client
// todo:: client might own more than one thread
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
// the kickoff of the survey

     $survey = get_survey_by_title(Proc::KICKOFF_SURVEY)[0];
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

// generation of a section
     $section_id = init_section_from_survey($thread_id, $survey->post_excerpt);
     if(is_null($section_id)){
          $message = esc_html(__('no section', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

// init of the panels of the new section
     $panels = init_panels_from_survey($section_id, $survey_id);

// session tickets
     set_session_ticket('thread_id', $thread_id, true);
     set_session_ticket('section_id', $section_id, true);

// result
     $coll = [];
     $coll['thread'] = get_thread_by_id($thread_id);
     $coll['section'] = get_sections_by_thread_id($thread_id);

     $message = esc_html(__('thread inited', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
     return true;
}



/***
   evaluates the *next linked section and the survey within
 */
add_action('admin_post_exec_get_next_section', 'exec_get_next_section');
function exec_get_next_section(){

// policy
     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

// read of current section
     $thread_id = get_session_ticket('thread_id');
     $section_id = get_session_ticket('section_id');

     $current_section = get_section_by_id($section_id)[0];
     if(is_null($current_section)){
          $message = esc_html(__('no such section', 'nosuch'));
          echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$section_id));
          return false;
     }

     $current_section->post_content = pagpick($current_section->post_content);

// eval of the redirect field
     $redirect = $current_section->post_content['survey']['settings']['redirect_after_submit_url'];

// evals ref of next survey
     preg_match('/\/to\/(.{0,124})#/', $redirect, $mtch);
     if(empty($mtch)){
          $message = esc_html(__('no next survey defined', 'nosuch'));
          echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$mtch));
          return false;
     }

// loads next section
     $next_section = get_section_by_ref($thread_id, $ref);
     if(!is_null($section)){
          $message = esc_html(__('next section loaded', 'nosuch'));
          echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$mtch));
          return true;
     }

// loads next survey
     $ref = $mtch[1];
     $survey = get_survey_by_ref($ref)[0];
     if(is_null($survey)){
          $message = esc_html(__('no such survey', 'nosuch'));
          echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$ref));
          return false;
     }
     $survey_id = $survey->ID;







// todo... whether or not section is already written
// generation of a section
     $section_id = init_section_from_survey($thread_id, $ref);
     if(empty($section_id)){
          $message = esc_html(__('could not init section', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

// generation of the panels of the section
     $panels = init_panels_from_survey($section_id, $survey_id);

// result
     $coll['thread'] = get_thread_by_id($thread_id);
     $coll['section'] = get_section_by_id($section_id);

// debug
     $coll['panels'] = $panels;

     set_session_ticket('section_id', $section_id, true);

     $message = esc_html(__('next section inited', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
     return true;
}

