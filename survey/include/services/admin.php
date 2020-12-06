<?php defined('ABSPATH') || exit;

add_action('admin_post_exec_clean_bookbuilder_db', 'exec_clean_bookbuilder_db');
function exec_clean_bookbuilder_db(){
     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     $res = clean_bookbuilder_db();
     $message = esc_html(__('bookbuilder db deleted', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'res'=>$res));
}

add_action('admin_post_exec_init_survey_page', 'exec_init_survey_page');
function exec_init_survey_page(){
     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     $res = init_survey_page();
     $message = esc_html(__('survey_page_inited', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'res'=>$res));
}
