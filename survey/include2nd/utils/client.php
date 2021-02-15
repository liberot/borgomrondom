<?php defined('ABSPATH') || exit;




function init_bb_thread(){

     $client_id = get_author_id();
     $thread_id = get_session_ticket('thread_id');
     if(!is_numeric($thread_id)){
          $thread_id = insert_thread($client_id);
     }
     else {
     }
     if(is_numeric($thread_id)){
          set_session_ticket('thread_id', $thread_id);
     }
}


/**
     sets the next field ref into the session
     kickoff is the one to start with
     the actions of a field eval to the next logic field
          depending on the input ( rec )
     and the survey jumps of the answers from the backend
*/
function set_next_field_ref(){

     $field = null;
     $field_ref = get_session_ticket('field_ref');
     if(is_null($field_ref)){
          $field = get_kickoff_field()[0];
     }
     else {
          $field = eval_next_field($field_ref);
     }

     set_session_ticket('field_ref', $field->ref);
}



/**
     evaluates the *next logic field
*/
function eval_next_field($field_ref){

     $field = null;
     $field = get_field_by_ref($field_ref)[0];

// eval of the action jump of the survey
     $actions = get_actions_of_field_by_ref($field_ref);
     if(empty($actions)){
          $pos = intval($field->pos);
          $pos = $pos+1;
          $field = get_field_of_survey_at_pos($field->survey_ref, $pos)[0];
     }
     else {
          $jumps = eval_jumps($actions);
          if(is_null($jumps)){
               $pos = intval($field->pos);
               $pos = $pos+1;
               $field = get_field_of_survey_at_pos($field->survey_ref, $pos)[0];
          }
          else {
               $link_ref = $jumps[0];
               $field = get_field_by_ref($link_ref)[0];
          }
     }

// eval of the survey jump
     $choice = get_choices_of_field($field_ref)[0];
     if(is_null($choice)){
     }
     else {
          $client_id = get_author_id();
          $thread_id = get_session_ticket('thread_id');
          $rec = get_rec_of_field($client_id, $thread_id, $field_ref)[0];
          if(is_null($rec)){
          }
          else {
               if($rec->doc == $choice->ref){
                    $link_ref = $choice->link_ref;
                    $field = get_field_by_ref($link_ref)[0];
               }
          }
     }

     return $field;
}



function eval_jumps($actions){

     $client_id = get_author_id();
     $jummps = [];
     foreach($actions as $action){

          $condition = json_decode(base64_decode($action->doc, true));
          if(is_null($condition)){
               continue;
          }

          foreach($condition->vars as $condition_var){
               $condition_field_ref = '';

               switch($condition_var->type){
                    case 'field':
                         $condition_field_ref = $condition_var->value;
                         break;
               }

               switch($condition_var->type){
                    case 'choice':
                         $res = is_rec_of_field_set_to($condition_field_ref, $condition_var->value);
                         if(!is_null($res)){
                         }
                         else {
                              $jumps[]= $action->link_ref;
                         }
                         break;

               }
          }
     }
}



function is_rec_of_field_set_to($field_ref, $choice_ref){

     $field_ref = esc_sql($field_ref);
     $choice_ref = esc_sql($choice_ref);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_rec 
          where field_ref = '{$field_ref}' 
          and doc = '{$choice_ref}' 
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}

function insert_thread($client_id){

     $client_id = esc_sql($client_id);
     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          insert into {$prefix}ts_bb_thread (client_id) values ('{$client_id}');
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->query($sql);
     return $wpdb->insert_id;
}



function get_thread_by_id($id){

     $id = esc_sql($id);
     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select * from {$prefix}ts_bb_thread where id = '{$id}';
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;

}



function decorate_field_title($field){

     $temp = $field->title;
     preg_match_all('/{{(.{42})}}/', $temp, $match);

     if(empty($match)){
     }
     else {
          $client_id = get_author_id();
          $thread_id = get_session_ticket('thread_id');
          foreach($match[1] as $m){
               $field_ref = preg_replace('/field:/', '', $m);
               $rec = get_rec_of_field($client_id, $thread_id, $field_ref)[0];
               $insert = '';
               if(is_null($rec)){
               }
               else {
                    $insert = $rec->doc;
               }
               $temp = str_replace($m, $insert, $temp);
          }
     }

     if(is_null($temp)){
     }
     else {
          $field->title = $temp;
     }

     return $field;
}



function process_incoming(){

     switch($_POST['cmd']){

          case 'reset_session':

               set_session_ticket('thread_id', null);
               set_session_ticket('field_ref', null);
               set_next_field_ref();

               wp_redirect('');

               break;

          case 'rec':

               $client_id = get_author_id();
               $thread_id = get_session_ticket('thread_id');
               $field_ref = get_session_ticket('field_ref');

               $ticket = trim_incoming_filename($_POST['ticket']);
               if($ticket != $field_ref){
                    break;
               }

               $answer = trim_incoming_string($_POST['answer']);
               if(is_null($answer)){
                    break;
               }

               if(1 >= strlen($answer)){
                    break;
               }

               $field = get_field_by_ref($field_ref)[0];

               $res = insert_bb_rec($client_id, $thread_id, $field, $answer);
               set_next_field_ref();

               break;
     }
}
