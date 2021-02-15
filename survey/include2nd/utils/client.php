<?php defined('ABSPATH') || exit;



add_action('init', 'init_thread');
add_action('init', 'set_next_field_ref');



function init_thread(){

// debug
     // set_session_ticket('thread_id', null);
     // set_session_ticket('field_ref', null);

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
               $field = get_field_of_survey_at_pos($field->survey_ref, $pos)[0];
          }
          else {
               $link_ref = $jumps[0];
               $field = get_field_by_ref($link_ref)[0];
          }
     }

// eval of the survey jump
     $choice = get_choice_of_field($field_ref)[0];
     if(is_null($choice)){
     }
     else {
          $client_id = get_author_id();
          $thread_id = get_session_ticket('thread_id');
          $rec = get_rec_of_field($client_id, $thread_id, $field_ref)[0];
          if(is_null($rec)){
          }
          else {
               if($rec->choice_ref == $choice->ref){
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
          select * from {$prefix}ts_bb_ref 
          where field_ref = '{$field_ref}' 
          and choice_ref = '{$choice_ref}' 
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
