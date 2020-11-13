<?php defined('ABSPATH') || exit;

add_action('init', 'exec_thread_test1st');
add_action('init', 'exec_thread_test2nd');
add_action('init', 'exec_thread_test3rd');
function exec_thread_test1st(){

     init_thread_utils();

     $nl = "\n";

     $conf = [];
     $conf['post_type'] = 'surveyprint_thread';
     $conf['post_title'] = random_string(32);
     $conf['post_name'] = random_string(32);
     $conf['post_content'] = random_string(32);

     $conf['meta_input'] = [
          'question_ids'=>'1000,1002,1003',
          'panel_ids'=>'8001,4004,9991',
          'question_refs'=>'10000000001,100000002,100000004',
          'panel_refs'=>'80000000001,400000000,99999999'
     ];

     $thread_id = init_thread($conf);
     print_r($survey_id);
     print $nl;

     $i = 107;
     while($i--){

          $conf = []; 
          $conf['post_type'] = 'surveyprint_panel';
          $conf['post_author'] = get_current_user_id();
          $conf['post_parent'] = $thread_id;
          $conf['post_title'] = random_string(32);
          $conf['post_name'] = random_string(32);
          $conf['post_content'] = random_string(32);

          $panel_id = init_panel($conf);
          print_r($panel_id);
          print $nl;
    }
}

function exec_thread_test2nd(){

     $res = get_threads_of_client();
     foreach($res as $thread){
          print_r(get_panels_by_thread_id($thread->ID));
     }
     
     print_r($res);
}

function exec_thread_test3rd(){

     print_r(get_threads_of_client());
     print_r(get_panels_by_thread_id('147502'));
     print_r(get_panels_by_thread_id('147502'));
}
