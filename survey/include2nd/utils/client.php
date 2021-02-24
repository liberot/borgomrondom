<?php defined('ABSPATH') || exit;


add_action('init', 'bb_init_thread');
function bb_init_thread(){

     if(!is_user_logged_in()){
          return;
     }

     $client_id = bb_get_author_id();
     $thread_id = bb_get_session_ticket('thread_id');
}



add_action('init', 'bb_process_incoming');
function bb_process_incoming(){

     if(!is_user_logged_in()){
          return;
     }

     switch($_POST['cmd']){

          case 'bb_init_new_thread':
                bb_init_new_thread();
                break;

          case 'bb_init_existing_thread':
                bb_init_existing_thread();
                break;

          case 'bb_write_rec':
                bb_write_rec();
                break;
     }
}


function bb_proceed_to_next_field(){

     $field_ref = bb_get_session_ticket('field_ref');
     $rec_pos = bb_get_session_ticket('rec_pos');

     $field = bb_eval_next_field($field_ref);
     if(is_null($field)){
          return;
     }

     bb_set_session_ticket('field_ref', $field->ref);

     $rec_pos = intval($rec_pos);
     $rec_pos = $rec_pos +1;
     bb_set_session_ticket('rec_pos', $rec_pos);
}



function bb_proceed_to_kickoff_field(){

     $client_id = bb_get_author_id();
     $field = bb_get_kickoff_field()[0];

     if(is_null($field)){
          return;
     }

     bb_set_session_ticket('field_ref', $field->ref);
     bb_set_session_ticket('rec_pos', 0);
}



/**
     evaluates the *next logic field
*/
function bb_eval_next_field($field_ref){

bb_add_debug_field('eval_next_field:', $field_ref);

     $client_id = bb_get_author_id();
     $thread_id = bb_get_session_ticket('thread_id');

// bossjump
// evaluates jumps to foreign surveys - to be adjuste in the wp admin area
     $choices = bb_get_choices_of_field($field_ref);
bb_add_debug_field('choices:', $choices);

     if(empty($choices)){
     }
     else {
          $rec = bb_get_rec_of_client_by_field_ref($client_id, $thread_id, $field_ref)[0];
          if(is_null($rec)){
          }
          else {
               foreach($choices as $choice){
                    if($rec->doc == $choice->ref){
                         $survey_ref = $choice->target_survey_ref;
                         $field = bb_get_first_field_of_survey_by_ref($survey_ref)[0];
                         if(is_null($field)){
                         }
                         else{
                              return $field;
                         }
                    }
               }
          }
     }

// logicjump (typeform)
// evaluates the action jumps of the survey descriptor
     $field = bb_get_field_by_ref($field_ref)[0];
     $actions = bb_get_actions_of_field_by_ref($field_ref);
     if(empty($actions)){
          $pos = intval($field->pos);
          $pos = $pos+1;
          $field = bb_get_field_of_survey_at_pos($field->survey_ref, $pos)[0];
     }
     else {
          $jumps = bb_eval_jumps($actions);
          if(empty($jumps)){
               $pos = intval($field->pos);
               $pos = $pos+1;
               $field = bb_get_field_of_survey_at_pos($field->survey_ref, $pos)[0];
bb_add_debug_field('no jumps, stepping to: ', $field);
bb_add_debug_field('at pos: ', $pos);
          }
          else {
               $link_ref = $jumps[0];
               $field = bb_get_field_by_ref($link_ref)[0];
               if(is_null($field)){
                    $field = bb_get_first_field_of_group($link_ref)[0];
bb_add_debug_field('jump found, stepping to: ', $field);
               }
          }
     }

     return $field;
}



/**
     evals the jump actions and their conditions that is mapped to the current field
*/
function bb_eval_jumps($actions){

     $client_id = bb_get_author_id();
     $thread_id = bb_get_session_ticket('thread_id');

     $jumps = [];

     foreach($actions as $action){

bb_add_debug_field('action', $action);
          $condition = json_decode(base64_decode($action->doc, true));

          if(is_null($condition)){
               continue;
          }
bb_add_debug_field('condition', $condition);

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

                         $rec = bb_get_rec_of_client_by_field_ref($client_id, $thread_id, $condition_field_ref)[0];
                         $val = $condition_var->value;
                         $rvl = $rec->doc;
                         // yes_no
                         preg_match('/^.{36}_(.{2,3})/', $rvl, $match);
                         if(!is_null($match[1])){
                              $rvl = 'yes' == $match[1] ? 'true' : 'false';
                         }

                         $condition_results[]= $rvl == $val ? 'true' : 'false';
                         break;

                    case 'choice':

                         $val = $condition_var->value;
                         $rec = bb_get_rec_of_client_by_field_ref($client_id, $thread_id, $condition_field_ref)[0];

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

bb_add_debug_field('jumps', $jumps);
     return $jumps;
}



function bb_decorate_field_title($field){

     $client_id = bb_get_author_id();
     $thread_id = bb_get_session_ticket('thread_id');

     $temp = $field->title;
     preg_match_all('/{{(.{42})}}/', $temp, $match);

     if(empty($match)){
     }
     else {
          foreach($match[1] as $m){
               $field_ref = preg_replace('/field:/', '', $m);
               $rec = bb_get_rec_of_client_by_field_ref($client_id, $thread_id, $field_ref)[0];
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



function bb_init_new_thread(){

     $client_id = bb_get_author_id();

     bb_set_session_ticket('thread_id', null);
     bb_set_session_ticket('field_ref', null);
     bb_set_session_ticket('rec_pos', null);

     $thread_id = bb_insert_thread($client_id);
     if(false == $thread_id){
         return;
     }

     bb_set_session_ticket('thread_id', $thread_id);

     bb_proceed_to_kickoff_field();

     wp_redirect('');
}



function bb_init_existing_thread(){

     $client_id = bb_get_author_id();
     $rec = bb_get_last_thread_of_client($client_id)[0];

     if(is_null($rec)){
     }
     else {

          $thread_id = $rec->id;
          $rec = bb_get_last_record_of_client($client_id, $thread_id)[0];
          if(is_null($rec)){
          }
          else {
               bb_set_session_ticket('client_id', $rec->client_id);
               bb_set_session_ticket('thread_id', $rec->thread_id);
               bb_set_session_ticket('field_ref', $rec->field_ref);
               bb_set_session_ticket('rec_pos', $rec->pos);
               wp_redirect('');
          }
     }
}



function bb_write_rec(){

     $client_id = bb_get_author_id();
     $thread_id = bb_get_session_ticket('thread_id');
     $field_ref = bb_get_session_ticket('field_ref');

     $rec_pos = bb_get_session_ticket('rec_pos');

     $ticket = bb_trim_incoming_filename($_POST['ticket']);
     if($ticket != $field_ref){
          return;
     }

     
     $field = bb_get_field_by_ref($field_ref)[0];
     if(is_null($field)){
          return;
     }

     if('file_upload' == $field->type){
          $assets = bb_get_assets_by_field_ref($client_id, $thread_id, $field_ref);
          if(empty($assets)){
               return;
          }
          else{
               bb_proceed_to_next_field();
               return;
          }
     }

     $answer = bb_trim_incoming_string($_POST['answer']);
     $answer = bb_trim_for_print($answer);
     if(empty($answer)){
          return;
     }

     $field = bb_get_field_by_ref($field_ref)[0];
     $res = bb_insert_rec($client_id, $thread_id, $field, $answer, $rec_pos);
     if(is_null($res)){
     }
     else {
          bb_proceed_to_next_field();
     }
}



