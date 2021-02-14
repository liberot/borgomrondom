<?php defined('ABSPATH') || exit;



add_action('admin_post_exec_insert_typeform_surveys', 'exec_insert_typeform_surveys');
function exec_insert_typeform_surveys(){

     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     init_log('exec_insert_typeform_surveys', []);

     $res = insert_typeform_surveys();

     $suc = 'failed';
     $message = esc_html(__('Typeform Survey Descriptors is NOt added to the DB', 'bookbuilder'));
     if(true == $res){
          $suc = 'success';
          $message = esc_html(__('Typeform Survey Descriptors is added to the DB', 'bookbuilder'));
     }

     echo json_encode(array('res'=>$suc, 'message'=>$message));
}




add_action('admin_post_exec_set_target_survey', 'exec_set_target_survey');
function exec_set_target_survey(){

     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     init_log('exec_set_target_survey', []);

     $choice_ref = trim_incoming_filename($_POST['choice_ref']);
     $target_survey_ref = trim_incoming_filename($_POST['target_survey_ref']);

     $res = set_target_survey($choice_ref, $target_survey_ref);

     $suc = 'failed';
     $message = esc_html(__('Target Survy is NOt set', 'bookbuilder'));
     if(true == $res){
          $suc = 'success';
          $message = esc_html(__('Target Survey is set', 'bookbuilder'));
     }

     echo json_encode(array('res'=>$suc, 'message'=>$message));

}
