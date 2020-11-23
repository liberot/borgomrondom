<?php defined('ABSPATH') || exit;

add_action('admin_post_exec_get_threads_of_client', 'exec_get_threads_of_client');
function exec_get_threads_of_client(){

     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $coll = get_threads_of_client();

     $message = esc_html(__('threads is loaded', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}

add_action('admin_post_exec_init_thread', 'exec_init_thread');
function exec_init_thread(){

// policy
     if(!policy_match([Role::ADMIN, Role::CUSTOMER, Role::SUBSCRIBER])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

// loads a previosly inited guest survey
// obollette since there is no guest client not authed
/*
     $unique_guest = get_session_ticket('unique_guest');
     $thread_id = get_session_ticket('thread_id');

     if(!is_null($thread_id)){
          $coll = [];
          $coll['thread'] = get_thread_by_id($thread_id);
          $coll['toc'] = get_toc_by_thread_id($thread_id);
          $coll['sections'] = get_sections_by_thread_id($thread_id);
     }

     if(!is_null($coll['thread'][0])){
          $message = esc_html(__('cached thread is loaded', 'nosuch'));
          echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll, 'ticket'=>$unique_guest));
          return true;
     }
*/

// todo
// client might own on or more threads
     $threads = get_threads_of_client();
     if(!is_null($threads[0])){
          $coll['thread'] = $threads;
          $coll['toc'] = get_toc_by_thread_id($coll['thread'][0]->ID);
          $coll['sections'] = get_sections_by_thread_id($coll['thread'][0]->ID);
          if(!is_null($coll['thread'])){
               set_session_ticket('thread_id', $coll['thread'][0]->ID, true);
               set_session_ticket('section_id', $coll['sections'][0]->ID, true);
               $message = esc_html(__('stored thread is loaded', 'nosuch'));
               echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll, 'ticket'=>$unique_guest));
               return true;
          }
     }

// inits a customer thread
     $author_id = get_author_id();
     $surveyprint_uuid = psuuid();
     $unique_guest = get_session_ticket('unique_guest');

     $survey = get_survey_by_title('__fielding_questions__')[0];
     // $survey = get_survey_by_title('Viktor Chapter 1 (copy)')[0];
     // $survey = get_survey_by_title('Viktor Cover and Preface (Yael) (copy)')[0];
     // $survey = get_survey_by_title('Fieldtypes')[0];

     if(is_null($survey)){
          $message = esc_html(__('no initial survey', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message, 'survey'=>$survey));
          return false;
     }

// inserts a post of type thread 
     $conf = [
          'post_type'=>'surveyprint_thread',
          'post_author'=>$author_id,
          'post_title'=>$surveyprint_uuid,
          'post_excerpt'=>$surveyprint_uuid,
          'post_name'=>$surveyprint_uuid,
          'post_content'=>$surveyprint_uuid
     ];

     $thread_id = init_thread($conf);
     if(0 == $thread_id){
          $message = esc_html(__('could not init a thread', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

// sets the session ticket thread id for incoming field validation
     set_session_ticket('thread_id', $thread_id, true);

// inserts a post of type section
     $surveyprint_uuid = psuuid();
     $conf = [
          'post_type'=>'surveyprint_section',
          'post_author'=>$author_id,
          'post_title'=>$unique_quest,
          'post_excerpt'=>$survey->post_excerpt,
          'post_name'=>$surveyprint_uuid,
          'post_content'=>$survey->post_content,
          'post_parent'=>$thread_id
     ];
     $section_id = init_section($conf);
     if(is_null($section_id)){
          $message = esc_html(__('no section', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     set_session_ticket('section_id', $section_id, true);

     if(Server::PRE_GENERATE_SECTION_PANELS) {
          $questions = get_questions_by_survey_id($survey->ID);
          foreach($questions as $question){
               $surveyprint_uuid = psuuid();
               $conf = [
                    'post_type'=>'surveyprint_panel',
                    'pos t_author'=>$author_id,
                    'post_title'=>$question->post_title,
                    'post_excerpt'=>$question->post_excerpt,
                    'post_name'=>$surveyprint_uuid,
                    'post_content'=>$question->post_content,
                    'post_parent'=>$section_id
              ];
              $panel_id = init_panel($conf);
          }
     }

// loads the toc 
     $toc = get_toc_by_survey_id($survey->ID)[0];
     if(is_null($toc)){
          $message = esc_html(__('no toc', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $surveyprint_uuid = psuuid();
     $conf = [
          'post_type'=>'surveyprint_toc',
          'post_title'=>$toc->post_title,
          'post_name'=>$surveyprint_uuid,
          'post_excerpt'=>$surveyprint_uuid,
          'post_parent'=>$thread_id,
          'post_content'=>$toc->post_content
     ];
     $toc_id = init_toc($conf);
     set_session_ticket('toc_id', $toc_id, true);

     $coll = [];
     $coll['thread'] = get_thread_by_id($thread_id);
     $coll['toc'] = get_toc_by_thread_id($thread_id);
     $coll['sections'] = get_sections_by_thread_id($thread_id);

// preloads the panels
     if(Server::PRE_GENERATE_SECTION_PANELS) {
          $coll['panels'] = [];
          $temp = clone $coll['toc'][0];
          $temp->post_content = pagpick($temp->post_content);
          foreach($temp->post_content['init_refs'] as $ref){
               $coll['panels'][] = get_panel_by_ref($section_id, $ref)[0];
          }
     }

     $message = esc_html(__('thread inited', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>'fielding questions inited', 'coll'=>$coll, 'ticket'=>$unique_guest));
     return true;
}
