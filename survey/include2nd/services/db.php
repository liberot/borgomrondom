<?php defined('ABSPATH') || exit;



add_action('admin_post_bb_delete_db', 'bb_delete_db');
function bb_delete_db(){

     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     // init_log('bb_delete_db', []);

     $res = bb_remove_v1_records();
     $res = bb_drop_tables();

     $suc = 'failed';
     $message = esc_html(__('Typform DB is NOt deleted', 'bookbuilder'));
     if(true == $res){
          $suc = 'success';
          $message = esc_html(__('Typeform DB is deleted', 'bookbuilder'));
     }

     echo json_encode(array('res'=>$suc, 'message'=>$message));
}



add_action('admin_post_bb_init_db', 'bb_init_db');
function bb_init_db(){

     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $res = bb_init_tables();
     $res = bb_init_conf();

     $suc = 'failed';
     $message = esc_html(__('Typform DB is NOt inited', 'bookbuilder'));
     if(true == $res){
          $suc = 'success';
          $message = esc_html(__('Typeform DB is inited', 'bookbuilder'));
     }

     echo json_encode(array('res'=>$suc, 'message'=>$message));
}



