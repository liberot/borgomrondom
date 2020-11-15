<?php defined('ABSPATH') || exit;

add_action('admin_post_exec_get_surveys', 'exec_get_surveys');
function exec_get_surveys(){
     if(!policy_match([Role::ADMIN, Role::CUSTOMER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     $coll = get_surveys();
     echo json_encode(array('res'=>'success', 'message'=>'surveys loaded', 'coll'=>$coll));
}

add_action('admin_post_exec_get_survey_by_id', 'exec_get_survey_by_id');
function exec_get_survey_by_id(){
     if(!policy_match([Role::ADMIN, Role::CUSTOMER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     $survey_id = trim_incoming_numeric($_POST['survey_id']);
     $coll = [];
     $coll['survey'] = get_survey_by_id($survey_id);
     $coll['threads'] = get_threads_by_survey_id($survey_id);
     $message = esc_html(__('survey is loaded', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}

add_action('admin_post_exec_save_question', 'exec_save_question');
function exec_save_question(){
     if(!policy_match([Role::ADMIN, Role::CUSTOMER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     $question_id = trim_incoming_numeric($_POST['id']);
     $max = trim_incoming_numeric($_POST['max']);
     $group = trim_incoming_string($_POST['group']);

     $coll = get_question_by_id($question_id)[0];
     if(null == $coll){
          $message = esc_html(__('no such question', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message, 'coll'=>$question_id));
          return false;
     }

     $coll->post_content = pagpick($coll->post_content);
     if(null != $max){
          $coll->post_content['conf']['max_assets'] = $max;
     }
     if(null != $group){
          $coll->post_content['conf']['layout_group'] = $group;
     }

     $coll->post_content = pigpack($coll->post_content);
     $res = wp_insert_post($coll);

     $message = esc_html(__('question is saved', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}


