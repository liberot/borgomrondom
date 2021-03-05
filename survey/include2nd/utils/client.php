<?php defined('ABSPATH') || exit;



add_shortcode('bb_client_view', 'bb_build_client_view');
function bb_build_client_view(){

     $client_id = bb_get_author_id();

     if(!is_user_logged_in()){
          echo '<p>ProfileBuilder Authentication Procedere</p>';
          echo do_shortcode('[wppb-login]');
          echo do_shortcode('[wppb-register]');
          echo do_shortcode('[wppb-recover-password]');
          return false;
     }

     $ticket = bb_get_ticket_of_client($client_id)[0];
     if(is_null($ticket)){
          $res = bb_init_existing_thread();
     }

     $ticket = bb_get_ticket_of_client($client_id)[0];
     if(is_null($ticket)){
          $res = bb_init_new_thread();
     }

     $ticket = bb_get_ticket_of_client($client_id)[0];
     if(is_null($ticket)){
          return false;
     }

     switch($ticket->view_state){

          case 'spread':
              bb_build_client_spread_view();
              break;

          case 'survey':
          default:
              bb_build_client_survey_view();
              break;
     }
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

          case 'bb_show_spreads':
                bb_show_spreads();
                break;

          case 'bb_show_survey':
                bb_show_survey();
                break;

          case 'bb_write_rec':
                bb_write_rec();
                bb_build_debug_spread();
                break;

     }
}


function bb_proceed_to_next_field(){

     $client_id = bb_get_author_id();
     $ticket = bb_get_ticket_of_client($client_id)[0];
     $field = bb_eval_next_field($ticket->field_ref);
     if(is_null($field)){
          return false;
     }

     $rec_pos = intval($ticket->rec_pos);
     $rec_pos = $rec_pos +1;

     $res = bb_set_ticket_of_client(
          $ticket->client_id,
          $ticket->thread_id,
          $field->ref,
          $rec_pos,
          'survey'
     );

     return $res;
}



function bb_proceed_to_kickoff_field($thread_id){

     $client_id = bb_get_author_id();
     $field = bb_get_kickoff_field()[0];

     if(is_null($field)){
          return false;
     }

     $res = bb_set_ticket_of_client(
          $client_id,
          $thread_id,
          $field->ref,
          0,
          'survey'
     );

     return $res;
}



/**
     evaluates the *next logic field
*/
function bb_eval_next_field($field_ref){

//bb_add_debug_field('eval_next_field:', $field_ref);

     $client_id = bb_get_author_id();
     $ticket = bb_get_ticket_of_client($client_id)[0];

// bossjump
// evaluates jumps to foreign surveys - to be adjuste in the wp admin area
     $choices = bb_get_choices_of_field($field_ref);
//bb_add_debug_field('choices:', $choices);

     if(empty($choices)){
     }
     else {
          $rec = bb_get_rec_of_client_by_field_ref($client_id, $ticket->thread_id, $field_ref)[0];
          if(is_null($rec)){
          }
          else {
               foreach($choices as $choice){

                    if('choice_of_no_choice' == $choice->title){
                         $survey_ref = $choice->target_survey_ref;
                         $field = bb_get_first_field_of_survey_by_ref($survey_ref)[0];
                         if(is_null($field)){
                         }
                         else{
                              return $field;
                         }
                    }

                    if($rec->choice_ref == $choice->ref){
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
//bb_add_debug_field('no jumps, stepping to: ', $field);
//bb_add_debug_field('at pos: ', $pos);
          }
          else {
               $link_ref = $jumps[0];
               $field = bb_get_field_by_ref($link_ref)[0];
               if(is_null($field)){
                    $field = bb_get_first_field_of_group($link_ref)[0];
//bb_add_debug_field('jump found, stepping to: ', $field);
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
     $ticket = bb_get_ticket_of_client($client_id)[0];

     $jumps = [];

     foreach($actions as $action){

//bb_add_debug_field('action', $action);
          $condition = json_decode(base64_decode($action->doc, true));

          if(is_null($condition)){
               continue;
          }
//bb_add_debug_field('condition', $condition);

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

                         $rec = bb_get_rec_of_client_by_field_ref($ticket->client_id, $ticket->thread_id, $condition_field_ref)[0];

                         $val = $condition_var->value;
                         $rvl = $rec->doc;                         

                         if('yes' == $rvl){ $rvl = 'true'; }
                         if('no' == $rvl){ $rvl = 'false'; }

                         $condition_results[]= $rvl == $val ? 'true' : 'false';
                         break;

                    case 'choice':

                         $val = $condition_var->value;
                         $rec = bb_get_rec_of_client_by_field_ref($ticket->client_id, $ticket->thread_id, $condition_field_ref)[0];
                         $condition_results[]= $rec->choice_ref == $val ? 'true' : 'false';

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

//bb_add_debug_field('jumps', $jumps);
     return $jumps;
}



function bb_decorate_field_title($field){

     $client_id = bb_get_author_id();

     $ticket = bb_get_ticket_of_client($client_id)[0];

     $temp = $field->title;
     preg_match_all('/{{(.{42})}}/', $temp, $match);

     if(empty($match)){
     }
     else {
          foreach($match[1] as $m){
               $field_ref = preg_replace('/field:/', '', $m);
               $rec = bb_get_rec_of_client_by_field_ref($ticket->client_id, $ticket->thread_id, $field_ref)[0];
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

     $thread_id = bb_insert_thread($client_id);
     if(false == $thread_id){
         return;
     }

     $res = bb_proceed_to_kickoff_field($thread_id);

     // wp_redirect('');
     return $res;
}



function bb_init_existing_thread(){

     $client_id = bb_get_author_id();
     $res = bb_get_last_thread_of_client($client_id)[0];
     if(is_null($res)){
     }
     else{
          $thread_id = $res->id;
          $res = bb_get_last_record_of_client($res->client_id, $thread_id)[0];
          if(is_null($res)){
                $res = bb_proceed_to_kickoff_field($thread_id);
          }
          else{
               $res = bb_set_ticket_of_client(
                    $res->client_id,
                    $res->thread_id,
                    $res->field_ref,
                    $res->rec_pos,
                    'survey'
                );
          }
     }
     return $res;
}



function bb_write_rec(){

     $client_id = bb_get_author_id();

     $ticket = bb_get_ticket_of_client($client_id)[0];

     $incoming_ticket = bb_trim_incoming_filename($_POST['ticket']);
     if($incoming_ticket != $ticket->field_ref){
          return;
     }

     $field = bb_get_field_by_ref($ticket->field_ref)[0];
     if(is_null($field)){
          return;
     }

     if('file_upload' == $field->type){
          $assets = bb_get_assets_by_field_ref($ticket->client_id, $ticket->thread_id, $field);
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

     $choice_ref = '';;
     $choices = bb_get_choices_of_field($field->ref);
     foreach($choices as $choice){
          if($answer == $choice->ref){
               $choice_ref = $choice->ref;
               $answer = $choice->title;
          }
          if('choice_of_no_choice' == $choice->title){
               $choice_ref = $choice->ref;
          }
     }

     $res = bb_insert_rec($ticket->client_id, $ticket->thread_id, $ticket->rec_pos, $field, $choice_ref, $answer);
     if(is_null($res)){
     }
     else {
          bb_proceed_to_next_field();
     }
}



function bb_show_spreads(){

     $client_id = bb_get_author_id();

     $ticket = bb_get_ticket_of_client($client_id)[0];

     $res = bb_set_ticket_of_client(
          $ticket->client_id,
          $ticket->thread_id,
          $ticket->field_ref,
          $ticket->rec_pos,
          'spread'
     );
}



function bb_show_survey(){

     $client_id = bb_get_author_id();

     $ticket = bb_get_ticket_of_client($client_id)[0];

     $res = bb_set_ticket_of_client(
          $ticket->client_id,
          $ticket->thread_id,
          $ticket->field_ref,
          $ticket->rec_pos,
          'survey'
     );
}




