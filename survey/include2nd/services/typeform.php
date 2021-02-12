<?php defined('ABSPATH') || exit;



add_action('admin_post_exec_insert_typeform_survey', 'exec_insert_typeform_survey');
function exec_insert_typeform_survey(){

     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $survey_file_name = trim_incoming_filename($_POST['survey_file_name']); 

     init_log('exec_insert_typeform_survey', ['survey_file_name']=>$survey_file_name);

     $res = $insert_typeform_survey($survey_file_name);

     $message = esc_html(__('Survey is parsed', 'bookbuilder'));
     $coll = ['survey_file_name'=>$survey_file_name]
     echo json_encode(array('res'=>'failed', 'message'=>$message, 'coll'=>$coll));
}


