<?php defined('ABSPATH') || exit;

add_action('admin_post_exec_delete_surveys', 'exec_delete_surveys');
function exec_delete_surveys(){
     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     init_log('admin_post_exec_delete_surveys', []);
     $res = delete_surveys();
     $message = esc_html(__('surveys deleted', 'bookbuilder'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'res'=>$res));
}

add_action('admin_post_exec_delete_client_threads', 'exec_delete_client_threads');
function exec_delete_client_threads(){
     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     init_log('admin_post_exec_delete_client_threads', []);
     $res = delete_client_threads();
     $message = esc_html(__('client threads deleted', 'bookbuilder'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'res'=>$res));
}

add_action('admin_post_exec_delete_bookbuilder_db', 'exec_delete_bookbuilder_db');
function exec_delete_bookbuilder_db(){
     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     init_log('admin_post_exec_delete_bookbuilder_db', []);
     $res = delete_bookbuilder_db();
     $message = esc_html(__('bookbuilder db deleted', 'bookbuilder'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'res'=>$res));
}

add_action('admin_post_exec_delete_layouts', 'exec_delete_layouts');
function exec_delete_layouts(){
     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     init_log('admin_post_exec_delete_layouts', []);
     $res = delete_layouts();
     $message = esc_html(__('layouts deleted', 'bookbuilder'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'res'=>$res));
}

add_action('admin_post_exec_delete_survey_page', 'exec_delete_survey_page');
function exec_delete_survey_page(){
     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     init_log('admin_post_exec_delete_survey_page', []);
     $res = delete_survey_page();
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
          // $file = sprintf('%s_%s', 'thread', trim_incoming_filename($thread->post_excerpt));
          $file = sprintf('%s_%s', 'thread', trim_incoming_filename(sprintf('%s_%s_%s', $thread->ID, $thread->post_author, $thread->post_title)));
          if(false == $file){ $file = 'no_filename'; }
          $path = sprintf('%s/%s.json', $path, $file);
          $dumps[]= $path;
          $res = @file_put_contents($path, $chunk);
     }

     $message = esc_html(__('threads dumped', 'bookbuilder'));

     echo json_encode(array('res'=>'success', 'message'=>$message, 'dumps'=>$dumps));
}

add_action('admin_post_exec_init_redirect', 'exec_init_redirect');
function exec_init_redirect(){

     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $question_id = trim_incoming_filename($_POST['question_id']);
     $survey_id = trim_incoming_filename($_POST['survey_id']);

     $coll = [];

     $coll['rec'] = init_redirect($question_id, $survey_id);

     $message = esc_html(__('redirect is set', 'bookbuilder'));
     $res = 'success';
     switch($coll['rec']){
          case false:
               $message = esc_html(__('redirect is not set', 'bookbuilder'));
               $res = 'failed';
               break;
     }

     echo json_encode(array('res'=>$res, 'message'=>$message, 'coll'=>$coll));
}

add_action('admin_post_exec_set_show_spread_state', 'exec_set_show_spread_state');
function exec_set_show_spread_state(){

     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $question_id = trim_incoming_filename($_POST['question_id']);
     $show_spread_state = trim_incoming_filename($_POST['show_spread_state']);

     $coll = [];

     $coll['rec'] = init_spread_state($question_id, $show_spread_state);

     $res = 'success';
     $message = esc_html(__('show spread state is set', 'bookbuilder'));
     switch($coll['rec']){
          case false:
               $message = esc_html(__('show spread state is not set', 'bookbuilder'));
               $res = 'failed';
               break;
     }

     echo json_encode(array('res'=>$res, 'message'=>$message, 'coll'=>$coll));
}
