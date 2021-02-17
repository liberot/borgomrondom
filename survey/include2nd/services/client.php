<?php defined('ABSPATH') || exit;



add_action('admin_post_exec_nav_prev_field', 'exec_nav_prev_field');
function exec_nav_prev_field(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $field_ref = get_session_ticket('field_ref');
     if(is_null($field_ref)){
          echo json_encode(array('res'=>'success', 'message'=>'no current field ref'));
          return;
     }

     $client_id = get_author_id();
     if(is_null($client_id)){
          echo json_encode(array('res'=>'success', 'message'=>'no client_id'));
          return;
     }

     $thread_id = get_session_ticket('thread_id');
     if(is_null($client_id)){
          echo json_encode(array('res'=>'success', 'message'=>'no thread_id'));
          return;
     }

     $rec = get_last_record_of_client($client_id, $thread_id)[0];
     if(is_null($rec)){
          echo json_encode(array('res'=>'success', 'message'=>'no rec'));
          return;
     }
     
     set_field_ref_to_ref($rec->field_ref);

     echo json_encode(array('res'=>'success', 'message'=>'prev_field', 'rec'=>$rec->field_ref));
}



