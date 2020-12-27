<?php defined('ABSPATH') || exit;

add_action('admin_post_exec_clean_surveys', 'exec_clean_surveys');
function exec_clean_surveys(){
     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     init_log('admin_post_exec_clean_surveys', []);
     $res = clean_surveys();
     $message = esc_html(__('surveys deleted', 'bookbuilder'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'res'=>$res));
}

add_action('admin_post_exec_clean_client_threads', 'exec_clean_client_threads');
function exec_clean_client_threads(){
     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     init_log('admin_post_exec_clean_client_threads', []);
     $res = clean_client_threads();
     $message = esc_html(__('client threads deleted', 'bookbuilder'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'res'=>$res));
}

add_action('admin_post_exec_clean_bookbuilder_db', 'exec_clean_bookbuilder_db');
function exec_clean_bookbuilder_db(){
     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     init_log('admin_post_exec_clean_bookbuilder_db', []);
     $res = clean_bookbuilder_db();
     $message = esc_html(__('bookbuilder db deleted', 'bookbuilder'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'res'=>$res));
}

add_action('admin_post_exec_clean_layouts', 'exec_clean_layouts');
function exec_clean_layouts(){
     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     init_log('admin_post_exec_clean_layouts', []);
     $res = clean_layouts();
     $message = esc_html(__('layouts deleted', 'bookbuilder'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'res'=>$res));
}

add_action('admin_post_exec_clean_survey_page', 'exec_clean_survey_page');
function exec_clean_survey_page(){
     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     init_log('admin_post_exec_clean_survey_page', []);
     $res = clean_survey_page();
     $message = esc_html(__('survey page deleted', 'bookbuilder'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'res'=>$res));
}

add_action('admin_post_exec_init_survey_page', 'exec_init_survey_page');
function exec_init_survey_page(){
     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     init_log('admin_post_exec_init_survey_page', []);
     $res = init_survey_page();
     $message = esc_html(__('survey page inited', 'bookbuilder'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'res'=>$res));
}

// todo: by id like by one each
add_action('admin_post_exec_dump_surveys', 'exec_dump_surveys');
function exec_dump_surveys(){
     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     init_log('admin_post_exec_dump_surveys', []);
     $res = dump_surveys();
     $dumps = [];
     foreach($res as $survey){
          $survey->post_content = pagpick($survey->post_content);
          $chunk = json_encode($survey, JSON_PRETTY_PRINT);
          $path = Path::get_backup_dir();
          $file = sprintf('%s_%s', 'survey', trim_incoming_filename($survey->post_excerpt));
          if(false == $file){ $file = 'no_filename'; }
          $path = sprintf('%s/%s.json', $path, $file);
          $dumps[]= $path;
          $res = @file_put_contents($path, $chunk);
     }
     $message = esc_html(__('surveys dumped', 'bookbuilder'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'dumps'=>$dumps));
}

add_action('admin_post_exec_dump_threads', 'exec_dump_threads');
function exec_dump_threads(){
     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     init_log('admin_post_exec_dump_threads', []);
     $res = dump_threads();
     $dumps = [];

// todo: client id and such
// todo: folder
// todo: panels of thread
// todo: assets of thread
// todo: book of thread
// todo: zip
     foreach($res as $thread){
          $thread->post_content = pagpick($thread->post_content);
          $chunk = json_encode($thread, JSON_PRETTY_PRINT);
          $path = Path::get_backup_dir();
          $file = sprintf('%s_%s', 'thread', trim_incoming_filename($thread->post_excerpt));
          if(false == $file){ $file = 'no_filename'; }
          $path = sprintf('%s/%s.json', $path, $file);
          $dumps[]= $path;
          $res = @file_put_contents($path, $chunk);
     }
     $message = esc_html(__('threads dumped', 'bookbuilder'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'dumps'=>$dumps));
}
