<?php defined('ABSPATH') || exit;



add_action('admin_post_exec_delete_db', 'exec_delete_db');
function exec_delete_db(){

     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     init_log('exec_delete_db', []);

     $res = drop_tables();
     $res = remove_v1_records();

     $suc = 'failed';
     $message = esc_html(__('Typform DB is NOt deleted', 'bookbuilder'));
     if(true == $res){
          $suc = 'success';
          $message = esc_html(__('Typeform DB is deleted', 'bookbuilder'));
     }

     echo json_encode(array('res'=>$suc, 'message'=>$message));
}



add_action('admin_post_exec_init_db', 'exec_init_db');
function exec_init_db(){

     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     init_log('exec_init_db', []);

     $res = init_tables();

     $suc = 'failed';
     $message = esc_html(__('Typform DB is NOt inited', 'bookbuilder'));
     if(true == $res){
          $suc = 'success';
          $message = esc_html(__('Typeform DB is inited', 'bookbuilder'));
     }

     echo json_encode(array('res'=>$suc, 'message'=>$message));
}


