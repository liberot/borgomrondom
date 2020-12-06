<?php defined('ABSPATH') || exit;

add_action('admin_post_exec_clean_surveys', 'exec_clean_surveys');
function exec_clean_surveys(){
     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     $res = clean_surveys();
     $message = esc_html(__('surveys deleted', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'res'=>$res));
}

add_action('admin_post_exec_clean_client_threads', 'exec_clean_client_threads');
function exec_clean_client_threads(){
     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     $res = clean_client_threads();
     $message = esc_html(__('client threads deleted', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'res'=>$res));
}

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
     $message = esc_html(__('survey page inited', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'res'=>$res));
}

// todo: by id like by one each
add_action('admin_post_exec_dump_surveys', 'exec_dump_surveys');
function exec_dump_surveys(){
     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     $res = dump_surveys();
     $dumps = [];
     foreach($res as $survey){
          $survey->post_content = pagpick($survey->post_content);
          $chunk = json_encode($survey, JSON_PRETTY_PRINT);
          $path = Path::get_backup_dir();
          $file = trim_incoming_filename($survey->post_excerpt);
          if(false == $file){ $file = 'no_filename'; }
          $path = sprintf('%s/%s.json', $path, $file);
          $dumps[]= $path;
          $res = @file_put_contents($path, $chunk);
     }
     $message = esc_html(__('surveys dumped', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'dumps'=>$dumps));
}
