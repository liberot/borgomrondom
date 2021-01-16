<?php defined('ABSPATH') || exit;

/***
   evaluates the *next linked section and the survey within
 */
add_action('admin_post_exec_get_next_section', 'exec_get_next_section');
function exec_get_next_section(){

// policy
     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     init_log('admin_post_exec_get_next_section', []);

// read of current section
     $thread_id = get_session_ticket('thread_id');
     $section_id = get_session_ticket('section_id');

     $current_section = get_section_by_id($thread_id, $section_id)[0];
     if(is_null($current_section)){
          $message = esc_html(__('no such section', 'bookbuilder'));
          echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$section_id));
          return false;
     }

     $current_section->post_content = pagpick($current_section->post_content);

// eval of the redirect field
     $redirect = $current_section->post_content['survey']['settings']['redirect_after_submit_url'];

// evals ref of next survey
     preg_match('/\/to\/(.{0,124})#/', $redirect, $mtch);
     if(empty($mtch)){
          $message = esc_html(__('no next survey defined', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message, 'coll'=>$redirect));
          return false;
     }
     $ref = '';
     $ref = $mtch[1];

// loads next section from db
     $next_section = get_section_by_ref($thread_id, $ref)[0];
     if(!is_null($next_section)){
          $message = esc_html(__('next section loaded', 'bookbuilder'));
          $coll['section'] = $next_section;
          $coll['redirect'] = $redirect;
          echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
          return true;
     }

// no next section found -> setup of the next section
// loads next survey
     $ref = $mtch[1];
     $survey = get_survey_by_ref($ref)[0];
     if(is_null($survey)){
          $message = esc_html(__('no such survey', 'bookbuilder'));
          echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$ref));
          return false;
     }
     $survey_id = $survey->ID;

// todo... whether or not section is already written
// setup of a section
     $section_id = init_section_from_survey_ref($thread_id, $ref);
     if(empty($section_id)){
          $message = esc_html(__('could not init section', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

// setup of the panels of the section
     $panels = init_panels_from_survey($section_id, $survey_id);

// result
     $coll['section'] = get_section_by_id($thread_id, $section_id)[0];
     $coll['redirect'] = $redirect;

     set_session_ticket('section_id', $section_id, true);

     $message = esc_html(__('next section inited', 'bookbuilder'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));

     return true;
}

add_action('admin_post_exec_get_section_by_survey_id', 'exec_get_section_by_survey_id');
function exec_get_section_by_survey_id(){

// policy
     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

// evall of the ids
     $thread_id = get_session_ticket('thread_id');
     $survey_id = trim_incoming_filename($_POST['survey_id']);

// 
     $coll = [];
//
     init_log('admin_post_exec_get_section_by_survey_id', ['survey_id'=>$survey_id]);

// load of the section
     $section = get_section_by_id($thread_id, $section_id)[0];
     if(!is_null($section)){
          $message = esc_html(__('cached section loaded', 'bookbuilder'));
          $res = 'succss';
          echo json_encode(array('res'=>$res, 'message'=>$message, 'coll'=>$coll));
          return true;
     }

// init of the section
     $section_id = init_section_from_survey_id($thread_id, $survey_id);
     $section = get_section_by_id($thread_id, $section_id)[0];

     $panels = init_panels_from_survey($section_id, $survey_id);

     set_session_ticket('section_id', $section_id, true);

// result
     $message = esc_html(__('section not inited', 'bookbuilder'));
     $res = 'failed';
     if(!is_null($section)){
          $message = esc_html(__('section inited', 'bookbuilder'));
          $res = 'success';
     }

     $coll['section'] = $section;
     $coll['panels'] = $panels;

     echo json_encode(array('res'=>$res, 'message'=>$message, 'coll'=>$coll));
}
