<?php defined('ABSPATH') || exit;



add_action('client_post_bb_nav_prev_field', 'bb_nav_prev_field');
function bb_nav_prev_field(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $client_id = bb_get_author_id();
     $thread_id = bb_get_session_ticket('thread_id');

     $rec_pos = bb_get_session_ticket('rec_pos');
     if(is_null($client_id)){
          echo json_encode(array('res'=>'success', 'message'=>'no rec_pos'));
          return;
     }

     $rec_pos = intval($rec_pos);
     $rec_pos = $rec_pos -1;
     if(0 >= $rec_pos){
          $rec_pos = 0;
     }

     $rec = bb_get_rec_of_client_by_rec_pos($client_id, $thread_id, $rec_pos)[0];
     if(is_null($rec)){
          echo json_encode(array('res'=>'success', 'message'=>'no rec'));
          return;
     }

     bb_set_session_ticket('field_ref', $rec->field_ref);
     bb_set_session_ticket('rec_pos', $rec->pos);
     bb_set_session_ticket('spreads', null);

     echo json_encode(array('res'=>'success', 'message'=>'prev_field', 'rec'=>$rec));
}



add_action('client_post_bb_upload_asset', 'bb_upload_asset');
function bb_upload_asset(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $client_id = bb_get_author_id();
     $thread_id = bb_get_session_ticket('thread_id');
     $field_ref = bb_get_session_ticket('field_ref');
     $rec_pos = bb_get_session_ticket('rec_pos');

     $scan = $_POST['scan'];

     $temp = base64_decode($scan['base'], true);
     $finf = new finfo(FILEINFO_MIME);
     $temp = $finf->buffer($temp);
     if(-1 == strpos('image/png', $temp)){
          $message = esc_html(__('Corrupt', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message, 'finf'=>$finf));
          return false;
     }

     $field = bb_get_field_by_ref($field_ref)[0];
     if(is_null($field)){
          $message = esc_html(__('No field', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message, 'finf'=>$finf));
          return false;
     }

     $res = bb_insert_asset($client_id, $thread_id, $field, $scan, $rec_pos);
     if(false == $res){
          $message = esc_html(__('No insert', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $answer = 'upload';
     $res = bb_insert_rec($client_id, $thread_id, $field, $answer, $rec_pos);

     $message = esc_html(__('File is uploaded', 'bookbuilder'));
     echo json_encode(array('res'=>'success', 'message'=>$message));
}




