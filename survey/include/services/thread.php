<?php defined('ABSPATH') || exit;

add_action('admin_post_exec_get_threads_of_client', 'exec_get_threads_of_client');
function exec_get_threads_of_client(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $coll = get_threads_of_client();

     $message = esc_html(__('threads is loaded', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}

add_action('admin_post_exec_init_thread', 'exec_init_thread');
function exec_init_thread(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $survey_id = trim_incoming_filename($_POST['survey_id']);
     $survey = get_survey_by_id($survey_id)[0]; 
     if(is_null($survey)){
          $message = esc_html(__('no such survey', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message, 'id'=>$survey_id));
          return false;
     }

     $surveyprint_uuid = psuuid();
     $thread_id = wp_insert_post([
          'post_type'=>'surveyprint_thread',
          'post_title'=>$survey->post_title,
          'post_author'=>get_author_id(),
          'post_name'=>$surveyprint_uuid,
          'post_excerpt'=>$survey->post_excerpt,
          'post_content'=>$survey->post_content,
          'post_parent'=>$survey_id
     ]);

     set_session_var('thread_id', $thread_id);

     $questions = get_questions_by_survey_id($survey_id);
     foreach($questions as $question){
          $surveyprint_uuid = psuuid();
          $conf = [
               'post_type'=>'surveyprint_panel',
               'post_author'=>get_current_user_id(),
               'post_title'=>$question->post_title,
               'post_name'=>$surveyprint_uuid,
               'post_excerpt'=>$question->post_excerpt,
               'post_parent'=>$thread_id,
               'post_content'=>$question->post_content
          ];
          $res = init_panel($conf);
     }

     $toc = get_toc_by_survey_id($survey_id)[0];
     if(is_null($toc)){
          $message = esc_html(__('no such ttoc', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message, 'survey_id'=>$survey_id));
          return false;
     }

     $surveyprint_uuid = psuuid();
     $conf = [
          'post_type'=>'surveyprint_toc',
          'post_title'=>$toc->post_title,
          'post_name'=>$surveyprint_uuid,
          'post_excerpt'=>$toc->post_excerpt,
          'post_parent'=>$thread_id,
          'post_content'=>$toc->post_content
     ];
     $res = init_toc($conf);
     $coll = [];
     $coll['thread'] = get_thread_by_id($thread_id);
     $coll['toc'] = get_toc_by_thread_id($thread_id);
     $coll['panels'] = [];
     $tmp = clone $coll['toc'][0];
     if(null != $tmp){ 
          $tmp->post_content = pagpick($tmp->post_content);
          foreach($tmp->post_content['init_refs'] as $ref){
               $coll['panels'][] = get_panel_by_ref($thread_id, $ref)[0];
          }
     }
     $message = esc_html(__('thread is inited', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}

add_action('admin_post_exec_get_thread_by_id', 'exec_get_thread_by_id');
function exec_get_thread_by_id(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $thread_id = trim_incoming_filename($_POST['thread_id']);
     set_session_var('thread_id', $thread_id);


     $coll = [];
     $coll['thread'] = get_thread_by_id($thread_id);
     $coll['toc'] = get_toc_by_thread_id($thread_id);
     $coll['panels'] = [];


     $tmp = clone $coll['toc'][0];
     if(null == $tmp){ 
          $message = esc_html(__('no toc', 'nosuch'));
          echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
          return false;
     }

     $tmp->post_content = pagpick($tmp->post_content);
     foreach($tmp->post_content['init_refs'] as $ref){
          $coll['panels'][] = get_panel_by_ref($thread_id, $ref)[0];
     }

     $message = esc_html(__('thread is loaded', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}


