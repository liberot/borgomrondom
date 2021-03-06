<?php defined('ABSPATH') || exit;



add_action('client_post_bb_load_current_document', 'bb_exec_load_current_document');
function bb_exec_load_current_document(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $coll = [];
     $client_id = bb_get_author_id();

     $ticket = bb_get_ticket_of_client($client_id)[0];
     if(is_null($ticket)){
          echo json_encode(array('res'=>'failed', 'message'=>'current document NOT loaded', 'coll'=>$coll));
     }

     $coll = bb_get_spreads_of_client_by_field_ref($ticket->client_id, $ticket->thread_id, $ticket->field_ref);

     echo json_encode(array('res'=>'success', 'message'=>'current document loaded', 'coll'=>$coll));
}



add_action('client_post_bb_nav_prev_field', 'bb_exec_nav_prev_field');
function bb_exec_nav_prev_field(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $res = bb_nav_prev_field();
     if(false == $res){
          echo json_encode(array('res'=>'failed', 'message'=>'prev_field'));
          return false;
     }

     echo json_encode(array('res'=>'success', 'message'=>'prev_field'));
}



add_action('client_post_bb_upload_asset', 'bb_exec_upload_asset');
function bb_exec_upload_asset(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $client_id = bb_get_author_id();

     $ticket = bb_get_ticket_of_client($client_id)[0];
     if(is_null($ticket)){
          echo json_encode(array('res'=>'success', 'message'=>'no ticket'));
          return;
     }

     $field = bb_get_field_by_ref($ticket->field_ref)[0];
     if(is_null($field)){
          $message = esc_html(__('no field', 'bookbuilder'));
          echo json_encode(array('res'=>'success', 'message'=>$message, 'finf'=>$finf));
          return false;
     }

     if($ticket->field_ref != $field->ref){
          $message = esc_html(__('corrupt', 'bookbuilder'));
          echo json_encode(array('res'=>'success', 'message'=>$message, 'finf'=>$finf));
          return false;
     }

     if('file_upload' != $field->type){
          $message = esc_html(__('corrupt', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message, 'finf'=>$finf));
          return false;
     }

     $res = bb_get_assetcount_of_field($ticket->client_id, $ticket->thread_id, $field->ref)[0];
     if(intval($res->max) >= intval(Proc::MAX_ASSETS_OF_FIELD)){
          $message = esc_html(__('no more assets', 'bookbuilder'));
          echo json_encode(array('res'=>'success', 'message'=>$message, 'finf'=>$finf));
          return false;
     }

     $scan = $_POST['scan'];
     $temp = base64_decode($scan['base'], true);
     $finf = new finfo(FILEINFO_MIME);
     $temp = $finf->buffer($temp);
     if(-1 == strpos('image/png', $temp)){
          $message = esc_html(__('corrupt', 'bookbuilder'));
          echo json_encode(array('res'=>'success', 'message'=>$message, 'finf'=>$finf));
          return false;
     }

     $res = bb_insert_asset($ticket->client_id, $ticket->thread_id, $ticket->rec_pos, $field, $scan);
     if(false == $res){
          $message = esc_html(__('no insert', 'bookbuilder'));
          echo json_encode(array('res'=>'success', 'message'=>$message));
          return false;
     }

     $answer = 'upload';
     $choice_ref = 'upload';
     $res = bb_insert_rec($ticket->client_id, $ticket->thread_id, $ticket->rec_pos, $field, $choice_ref, $answer);

     $message = esc_html(__('file is uploaded', 'bookbuilder'));

     echo json_encode(array('res'=>'success', 'message'=>$message));
}



/***
add_action('client_post_bb_set_hidden_fields', 'bb_exec_bb_set_hidden_fields');
function bb_exec_bb_set_hidden_fields(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $client_id = bb_get_author_id();

     $ticket = bb_get_ticket_of_client($client_id)[0];
     if(is_null($ticket)){
          echo json_encode(array('res'=>'success', 'message'=>'no ticket'));
          return;
     }

     $fields = bb_trim_incoming_hidden_fields($_POST['fields']);

     $res = bb_insert_hidden_fields($ticket->client_id, $ticket->thread_id, $fields);
     if(false == $res){
          $message = esc_html(__('hidden fields is NOT set', 'bookbuilder'));
          echo json_encode(array('res'=>'success', 'message'=>$message, 'fields'=>$fields));
          return false;
     }

     $message = esc_html(__('hidden fields is set', 'bookbuilder'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'fields'=>$fields));
};
*/





