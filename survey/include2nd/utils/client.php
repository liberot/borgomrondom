<?php defined('ABSPATH') || exit;


add_action('init', 'init_bb_thread');
function init_bb_thread(){

     if(!is_user_logged_in()){
          return;
     }

     $client_id = get_author_id();
     $thread_id = get_session_ticket('thread_id');
}



add_action('init', 'process_incoming');
function process_incoming(){

     if(!is_user_logged_in()){
          return;
     }

     switch($_POST['cmd']){

          case 'init_new_thread':
                init_new_thread();
                break;

          case 'init_existing_thread':
                init_existing_thread();
                break;

          case 'rec':
                write_rec();
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
function proceed_to_next_field(){

     $field_ref = get_session_ticket('field_ref');
     $rec_pos = get_session_ticket('rec_pos');

     $field = eval_next_field($field_ref);
     if(is_null($field)){
          return;
     }

     set_session_ticket('field_ref', $field->ref);

     $rec_pos = intval($rec_pos);
     $rec_pos = $rec_pos +1;
     set_session_ticket('rec_pos', $rec_pos);
}



function proceed_to_kickoff_field(){

     $client_id = get_author_id();
     $field = get_kickoff_field()[0];

     if(is_null($field)){
          return;
     }

     set_session_ticket('field_ref', $field->ref);
     set_session_ticket('rec_pos', 0);
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
          $rec = get_rec_of_client_by_field_ref($client_id, $thread_id, $field_ref)[0];
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
               $field = get_field_by_ref($link_ref)[0];
               if(is_null($field)){
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
                         $rec = get_rec_of_client_by_field_ref($client_id, $thread_id, $condition_field_ref)[0];
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
               $rec = get_rec_of_client_by_field_ref($client_id, $thread_id, $field_ref)[0];
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
          $temp = str_replace('{{', '', $temp);
          $temp = str_replace('}}', '', $temp);
          $field->title = $temp;
     }

     return $field;
}



function init_new_thread(){

     $client_id = get_author_id();

     set_session_ticket('thread_id', null);
     set_session_ticket('field_ref', null);
     set_session_ticket('rec_pos', null);

     $thread_id = insert_thread($client_id);
     if(false == $thread_id){
          return;
     }

     set_session_ticket('thread_id', $thread_id);

     proceed_to_kickoff_field();

     wp_redirect('');
}



function init_existing_thread(){

     $client_id = get_author_id();
     $rec = get_last_thread_of_client($client_id)[0];

     if(is_null($rec)){
     }
     else {

          $thread_id = $rec->id;
          $rec = get_last_record_of_client($client_id, $thread_id)[0];
          if(is_null($rec)){
          }
          else {
               set_session_ticket('client_id', $rec->client_id);
               set_session_ticket('thread_id', $rec->thread_id);
               set_session_ticket('field_ref', $rec->field_ref);
               set_session_ticket('rec_pos', $rec->pos);
               wp_redirect('');
          }
     }
}



function write_rec(){

     $client_id = get_author_id();
     $thread_id = get_session_ticket('thread_id');
     $field_ref = get_session_ticket('field_ref');

     $rec_pos = get_session_ticket('rec_pos');

     $ticket = trim_incoming_filename($_POST['ticket']);
     if($ticket != $field_ref){
          return;
     }

     $answer = trim_incoming_string($_POST['answer']);
     $answer = trim_for_print($answer);
     if(is_null($answer)){
          return;
     }

     if(empty($answer)){
          return;
     }

     $field = get_field_by_ref($field_ref)[0];
     $res = insert_bb_rec($client_id, $thread_id, $rec_pos, $field, $answer);
     if(is_null($res)){
     }
     else {
          proceed_to_next_field();
     }
}



