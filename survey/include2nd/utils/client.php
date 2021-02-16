<?php defined('ABSPATH') || exit;



add_action('init', 'init_bb_thread');
function init_bb_thread(){

     if(!is_user_logged_in()){
          return;
     }

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



add_action('init', 'process_incoming');
function process_incoming(){

     if(!is_user_logged_in()){
          return;
     }

     switch($_POST['cmd']){

          case 'reset_session':

               set_session_ticket('thread_id', null);
               set_session_ticket('field_ref', null);
               init_bb_thread();
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

     $client_id = get_author_id();
     $thread_id = get_session_ticket('thread_id');

     $field = null;

// evaluates jump to another survey from the wp backend
     $choices = get_choices_of_field($field_ref);
     if(empty($choices)){
     }
     else {
          $rec = get_rec_of_field($client_id, $thread_id, $field_ref)[0];
          if(is_null($rec)){
          }
          else {
               foreach($choices as $choice){
                    if($rec->doc == $choice->ref){
                         $survey_ref = $choice->target_survey_ref;
                         $field = get_first_field_of_survey_by_ref($survey_ref)[0];
                         if(is_null($field)){
                         }
                         else{
                              return $field;
                         }
                    }
               }
          }
     }


// evaluates the action jumps of the survey descriptor
     $field = get_field_by_ref($field_ref)[0];
     $actions = get_actions_of_field_by_ref($field_ref);
     if(empty($actions)){
          $pos = intval($field->pos);
          $pos = $pos+1;
          $field = get_field_of_survey_at_pos($field->survey_ref, $pos)[0];
     }
     else {
          $jumps = eval_jumps($actions);
          if(empty($jumps)){
               $pos = intval($field->pos);
               $pos = $pos+1;
               $field = get_field_of_survey_at_pos($field->survey_ref, $pos)[0];
          }
          else {
               $link_ref = $jumps[0];

// fixdiss: groups is fields also
               $field = get_field_by_ref($link_ref)[0];
               if(is_null($field)){
print_r($link_ref);
                    $field = get_first_field_of_group($link_ref)[0];
               }
          }
     }

     return $field;
}



/**
     evals the jump actions and their conditions that is mapped to the current field
*/
function eval_jumps($actions){

     $client_id = get_author_id();
     $thread_id = get_session_ticket('thread_id');

     $jumps = [];

     foreach($actions as $action){

          $condition = json_decode(base64_decode($action->doc, true));

          if(is_null($condition)){
               continue;
          }

debug_field_add('action', $action);
debug_field_add('condition', $condition);

          // is or and always...
          $op = $condition->op;

          // variables
          $condition_results = [];
          $condition_field_ref;
          foreach($condition->vars as $condition_var){

               switch($condition_var->type){

                    case 'field':
                         $condition_field_ref = $condition_var->value;
                         break;
               }
          }

          foreach($condition->vars as $condition_var){
               switch($condition_var->type){

                    case 'constant':

                         $val = $condition_var->value;
                         $val = false == $val ? 'false' : 'true';

                         break;

                    case 'choice':

                         $val = $condition_var->value;
                         $rec = get_rec_of_field($client_id, $thread_id, $condition_field_ref)[0];
                         $condition_results[]= $rec->doc == $val ? 'true' : 'false';

                         break;


               }
          }

          switch($op){

               case 'is':
               case 'and':
                    if(false === array_search('false', $condition_results)){
                         $jumps[]= $action->link_ref;
                    }
                    break;

               case 'or':
                    if(false !== array_search('true', $condition_results)){
                         $jumps[]= $action->link_ref;
                    }
                    break;
          }

          switch($op){

               case 'always':
                    if(empty($jumps)){
                         $jumps[]= $action->link_ref;
                    }
                    break;
          }
     }

debug_field_add('jumps', $jumps);

     return $jumps;
}



/**
     adds a new thread
*/
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

     $client_id = get_author_id();
     $thread_id = get_session_ticket('thread_id');

     $temp = $field->title;
     preg_match_all('/{{(.{42})}}/', $temp, $match);

     if(empty($match)){
     }
     else {
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



