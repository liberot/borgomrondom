<?php defined('ABSPATH') || exit;



add_action('admin_post_bb_insert_typeform_surveys', 'bb_exec_insert_typeform_surveys');
function bb_exec_insert_typeform_surveys(){

     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $res = bb_insert_typeform_surveys();

     $suc = 'failed';
     $message = esc_html(__('Typeform Survey Descriptors is NOt added to the DB', 'bookbuilder'));
     if(true == $res){
          $suc = 'success';
          $message = esc_html(__('Typeform Survey Descriptors is added to the DB', 'bookbuilder'));
     }

     echo json_encode(array('res'=>$suc, 'message'=>$message));
}




add_action('admin_post_bb_set_target_survey', 'bb_exec_set_target_survey');
function bb_exec_set_target_survey(){

     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $choice_ref = bb_trim_incoming_filename($_POST['choice_ref']);
     $target_survey_ref = bb_trim_incoming_filename($_POST['target_survey_ref']);

     $res = bb_set_target_survey_ref($choice_ref, $target_survey_ref);

     $suc = 'failed';
     $message = esc_html(__('Target Survy is NOt set', 'bookbuilder'));
     if(true == $res){
          $suc = 'success';
          $message = esc_html(__('Target Survey is set', 'bookbuilder'));
     }

     echo json_encode(array('res'=>$suc, 'message'=>$message));

}



add_action('admin_post_bb_set_root_survey', 'bb_exec_set_root_survey');
function bb_exec_set_root_survey(){

     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $target_survey_title = bb_trim_incoming_filename($_POST['target_survey_title']);

     $res = bb_set_root_survey_title($target_survey_title);

     $suc = 'failed';
     $message = esc_html(__('Root Survey is NOt set', 'bookbuilder'));
     if(true == $res){
          $suc = 'success';
          $message = esc_html(__('Root Survey is set', 'bookbuilder'));
     }

     echo json_encode(array('res'=>$suc, 'message'=>$message));
}



