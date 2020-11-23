<?php defined('ABSPATH') || exit;

add_action('admin_post_exec_get_panels_of_client', 'exec_get_panels_of_client');
function exec_get_panels_of_client(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $coll = [];
     $res = get_threads_of_client();
     foreach($res as $thread){
          $coll[]= get_panels_by_thread_id($thread->ID);
     }
     
     $message = esc_html(__('panels is loaded', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}

add_action('admin_post_exec_get_panels_by_thread_id', 'exec_get_panels_by_thread_id');
function exec_get_panels_by_thread_id(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $thread_id = trim_incoming_numeric($_POST['thread_id']);
     $thread_id = get_session_ticket('thread_id');

     $coll = get_panels_by_thread_id($thread_id);
     $message = esc_html(__('panels is loaded', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}

add_action('admin_post_exec_init_panel', 'exec_init_panel');
function exec_init_panel(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $author_id = get_author_id();

     $panel_ref = trim_incoming_filename($_POST['panel_ref']);
     $panel_ref = get_session_ticket('panel_ref');

     if(is_null($panel_ref)){
          $message = esc_html(__('panel corrupt', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $thread_id = trim_incoming_filename($_POST['thread_id']);
     $thread_id = get_session_ticket('thread_id');
     if(is_null($thread_id)){
          $message = esc_html(__('thread corrupt', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $section_id = $_POST['section_id'];
     $section_id = get_session_ticket('section_id');
     if(is_null($section_id)){
          $message = esc_html(__('section corrupt', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $question = trim_incoming_string($_POST['question']);
     $question = trim_for_print($question);

     $answer = trim_incoming_string($_POST['answer']);
     $answer = trim_for_print($answer);

     $panel = get_panel_by_ref($section_id, $panel_ref)[0];
     if(is_null($panel)){
          $message = esc_html(__('no such panel', 'nosuch'));
          echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$panel_ref));
          return false;
     }

     $doc = pagpick($panel->post_content);
     $doc['question'] = $question;
     $doc['answer'] = $answer;
     $doc = pigpack($doc);

     $conf = [
          'post_author'=>$author_id,
          'post_type'=>'surveyprint_panel',
          'post_parent'=>$section_id,
          'post_excerpt'=>$panel_ref,
          'post_content'=>$doc
     ];

     if(Server::UPDATE_ON_PERSIST){
          $panel = get_panel_by_ref($section_id, $panel_ref)[0];
          if(is_null($panel)){
               $message = esc_html(__('no such panel', 'nosuch'));
               echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$panel_ref));
               return false;
          }
          $conf['ID'] = $panel->ID;
     }

     $panel_id = init_panel($conf);

     // $coll = get_panel_by_ref($section_id, panel_id);
     $coll = [];

     $message = esc_html(__('panel saved', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}

add_action('admin_post_exec_get_panel_by_ref', 'exec_get_panel_by_ref');
function exec_get_panel_by_ref(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $section_id = trim_incoming_filename($_POST['section_id']);
     $section_id = get_session_ticket('section_id');
     if(is_null($section_id)){
          $message = esc_html(__('section corrupt', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $panel_ref = trim_incoming_filename($_POST['panel_ref']);
     if(is_null($panel_ref)){
          $message = esc_html(__('ref corrupt', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     
     set_session_ticket('panel_ref', $panel_ref, true);

// sets up a panel
     if(false == Server::PRE_GENERATE_SECTION_PANELS){

          $coll = get_panel_by_ref($section_id, $panel_ref);
          if(!is_null($coll[0])){
               $message = esc_html(__('cached panel is loaded', 'nosuch'));
               echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
               return true;
          }

          $section = get_section_by_id($section_id)[0];
          if(is_null($section)){
               $message = esc_html(__('section corrupt', 'nosuch'));
               echo json_encode(array('res'=>'failed', 'message'=>$message));
               return false;
          }

          $section->post_content = pagpick($section->post_content);
          $title = $section->post_content['title'];

          $survey = get_survey_by_title($title)[0];
          if(is_null($survey)){
               $message = esc_html(__('survey corrupt', 'nosuch'));
               echo json_encode(array('res'=>'failed', 'message'=>$message));
               return false;
          }

          $question = get_question_by_ref($survey->ID, $panel_ref)[0];
          if(is_null($question)){
               $message = esc_html(__('question corrupt', 'nosuch'));
               echo json_encode(array('res'=>'failed', 'message'=>$message));
               return false;
          }

          $surveyprint_uuid = psuuid();
          $auhor_id = get_author_id();
          $conf = [
               'post_type'=>'surveyprint_panel',
               'post_author'=>$author_id,
               'post_title'=>$question->post_title,
               'post_excerpt'=>$question->post_excerpt,
               'post_name'=>$surveyprint_uuid,
               'post_content'=>$question->post_content,
               'post_parent'=>$section_id
          ];

          $panel_id = init_panel($conf);
     }

// loads the panel
     $coll = get_panel_by_ref($section_id, $panel_ref);

     $message = esc_html(__('panel is loaded', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}

add_action('exec_test', 'test');
function test(){
    print_r($_POST);
}

