<?php defined('ABSPATH') || exit;



add_action('client_post_exec_nav_prev_field', 'exec_nav_prev_field');
function exec_nav_prev_field(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $client_id = get_author_id();
     $thread_id = get_session_ticket('thread_id');

     $rec_pos = get_session_ticket('rec_pos');
     if(is_null($client_id)){
          echo json_encode(array('res'=>'success', 'message'=>'no rec_pos'));
          return;
     }

     $rec_pos = intval($rec_pos);
     $rec_pos = $rec_pos -1;
     if(0 >= $rec_pos){
          $rec_pos = 0;
     }

     $rec = get_rec_of_client_by_rec_pos($client_id, $thread_id, $rec_pos)[0];
     if(is_null($rec)){
          echo json_encode(array('res'=>'success', 'message'=>'no rec'));
          return;
     }

     set_session_ticket('field_ref', $rec->field_ref);
     set_session_ticket('rec_pos', $rec->pos);

     echo json_encode(array('res'=>'success', 'message'=>'prev_field', 'rec'=>$rec));
}



