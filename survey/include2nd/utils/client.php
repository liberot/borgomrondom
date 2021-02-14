<?php defined('ABSPATH') || exit;



add_action('init', 'init_thread');
add_action('init', 'step_to_next_field');



function init_thread(){

     // set_session_ticket('thread_id', null);

     $client_id = get_author_id();
     $thread_id = get_session_ticket('thread_id');
     if(is_null($thread_id)){
          $thread_id = insert_thread($client_id);
     }
     else {
     }
     if(is_numeric($thread_id)){
          set_session_ticket('thread_id', $thread_id);
     }
print 'thread_id:';
print_r(get_session_ticket('thread_id'));
}



function step_to_next_field(){

     $field = null;
     $field_ref = get_session_ticket('field_ref');
     if(is_null($field_ref)){
          $field = get_kickoff_field()[0];
     }
     else {
          $field = eval_next_field($field_ref);
     }
     set_session_ticket('field_ref', $field->ref);
print 'field_ref:';
print_r($field_ref);
print_r($field);
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
          $field = eval_actions($actions);
     }
     return $field;
}



function eval_actions($actions){

     $client_id = get_author_id();
     foreach($actions as $action){
print_r($action);
     }
}



function insert_thread($client_id){

     $client_id = esc_sql($client_id);
     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          insert into {$prefix}ts_bb_thread (client_id) values ('{$client_id}');
EOD;
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
     $res = $wpdb->get_results($sql);
     return $res;

}
