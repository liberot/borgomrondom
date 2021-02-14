<?php defined('ABSPATH') || exit;



add_action('init', 'set_next_field');



function set_next_field(){

     $field = null;
     $field_ref = get_session_ticket('field_ref');

     if(is_null($field_ref)){

          $field = get_kickoff_field()[0];
     }
     else {

          $field = eval_next_field($field_ref);
     }

     set_session_ticket('field_ref', $field->ref);

     return $field_ref;
}



function eval_next_field($field_ref){

     $field = null;

     $field = get_field_by_ref($field_ref)[0];

     $actions = get_actions_of_field_by_ref($field_ref);
     if(empty($actions)){
          $pos = intval($field->pos);
          $pos = $pos+1;
          $field = get_field_of_survey_at_pos($field->survey_ref, $pos)[0];
     }
     else {
print_r($actions);

     }

     return $field;
}




