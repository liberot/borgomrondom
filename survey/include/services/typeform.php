<?php defined('ABSPATH') || exit;

add_action('admin_post_exec_edit_typeform_survey', 'exec_edit_typeform_survey');
function exec_edit_typeform_survey(){

     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $ref = trim_incoming_string($_POST['bucket']); 
     $coll = get_typeform_surveys_by_ref($ref);

     $message = esc_html(__('edit', 'nosuch'));
     echo json_encode(array('res'=>'failed', 'message'=>$message, 'coll'=>$coll));
}

add_action('admin_post_exec_construct_typeform_survey', 'exec_construct_typeform_survey');
function exec_construct_typeform_survey(){

     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

// sets file name that is to be parsed
     $survey_file_name = trim_incoming_filename($_POST['survey_file_name']);
     if(is_null($survey_file_name)){
          $survey_file_name = 'typeform_survey.json';
     }

// reads tpeform survey json
     $ds = DIRECTORY_SEPARATOR;
     $path = WP_PLUGIN_DIR.SURVeY.$ds.'asset'.$ds.$survey_file_name;
     $data = @file_get_contents($path);
     if(is_null($data)){
          $message = esc_html(__('no json', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message, 'path'=>$path));
          return false;
     }

// parses document
     $doc = json_decode($data);
     if(is_null($doc)){
          $message = esc_html(__('no document: ', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message.$path));
          return;
     }
     $doc = walk_the_doc($doc);

// inserts a post of type survey
     $survey_type = 'typeform'; 
     $survey_ref = $doc['id'];
     $survey_title = $doc['title'];

     $uuid = psuuid();
     $survey_id = wp_insert_post([
          'post_type'=>'surveyprint_survey',
          'post_title'=>$survey_title,
          'post_name'=>$survey_type,
          'post_excerpt'=>$survey_ref,
          'post_content'=>pigpack($doc)
     ]);

     if(is_null($survey_id)){
          $message = esc_html(__('no survey write', 'nosuch'));
          echo json_encode(
               [ 'res'=>'failed', 'message'=>$message, 'survey_id'=>$survey_id, 'typeform_ref'=>$typeform_ref, 'uuid'=>$uuid ]
          );
          return;
     }

// insert posts of type question 
     $survey_type = 'typeform'; 
     $refs = [];
     $ids = [];
     foreach($doc['fields'] as $field){
          switch($field['type']){
               case 'group':
                    $conf = [];
                    $conf['layout_group'] = 'default';
                    $conf['max_asset'] = '0';
                    if('file_upload' == $field['type']){ $conf['max_asset'] = '1'; }
                    $field['conf'] = $conf;
                    $surveyprint_uuid = psuuid();
                    $conf = [ 
                         'post_type'=>'surveyprint_question',
                         'post_title'=>$field['title'],
                         'post_excerpt'=>$field['ref'],
                         'post_name'=>$surveyprint_uuid,
                         'post_parent'=>$survey_id,
                         'post_content'=>pigpack($field)
                    ];
                    $question_id = wp_insert_post($conf);
                    $refs[]= $field['ref'];
                    $ids[]= $question_id;
                    foreach($field['properties']['fields'] as $gield){
                         $conf = [];
                         $conf['layout_group'] = 'default';
                         $conf['max_asset'] = '0';
                         if('file_upload' == $gield['type']){ $conf['max_asset'] = '1'; }
                         $gield['conf'] = $conf;
                         $surveyprint_uuid = psuuid();
                         $conf = [ 
                              'post_type'=>'surveyprint_question',
                              'post_title'=>$gield['title'],
                              'post_excerpt'=>$gield['ref'],
                              'post_name'=>$surveyprint_uuid,
                              'post_parent'=>$survey_id,
                              'post_content'=>pigpack($gield)
                         ];
                         $question_id = wp_insert_post($conf);
                         $refs[]= $gield['ref'];
                         $ids[]= $question_id;
                    }
                    break;
               default:
                    $conf = [];
                    $conf['layout_group'] = 'default';
                    $conf['max_asset'] = '0';
                    if('file_upload' == $field['type']){ $conf['max_asset'] = '1'; }
                    $field['conf'] = $conf;
                    $surveyprint_uuid = psuuid();
                    $conf = [ 
                         'post_type'=>'surveyprint_question',
                         'post_title'=>$field['title'],
                         'post_excerpt'=>$field['ref'],
                         'post_name'=>$surveyprint_uuid,
                         'post_parent'=>$survey_id,
                         'post_content'=>pigpack($field)
                    ];
                    $question_id = wp_insert_post($conf);
                    $refs[]= $field['ref'];
                    $ids[]= $question_id;
          }
     }

// inserts a post of type toc 
     $post_content = [];
     $post_content['toc'] = [];
     $post_content['rulez'] = $doc['logic'];
     $post_content['init_refs'] = $refs;
     $post_content['init_ids'] = $ids;
     $conf = [
          'post_type'=>'surveyprint_toc',
          'post_title'=>$survey_title,
          'post_name'=>$survey_type,
          'post_excerpt'=>$survey_ref,
          'post_parent'=>$survey_id,
          'post_content'=>pigpack($post_content)
     ];
     $toc_id = init_toc($conf);

     if(is_null($toc_id)){
          $message = esc_html(__('no toc write', 'nosuch'));
          echo json_encode([ 'res'=>'failed', 'message'=>$message ]);
          return;
     }

     $message = sprintf('survey added: %s: %s', $survey_id, $survey_title);
     echo json_encode(array('res'=>'success', 'message'=>$message, 'refs'=>$refs));
}

add_action('admin_post_exec_download_typeform_survey', 'exec_download_typeform_survey');
function exec_download_typeform_survey(){
     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return;
     }
     $typeform_base = 'https://api.typeform.com/forms';

     $bucket = trim_incoming_filename($_POST['bucket']);
     $auth_token = trim_incoming_string($_POST['auth_token']);
     $type = trim_incoming_filename($_POST['type']);

     $loc = '';
     switch($type){
          case 'form':
               $loc = $typeform_base.'/'.$bucket;
               break;
          case 'result':
               $loc = $typeform_base.'/'.$bucket.'/responses';
               break;
     }
     $doc = fetch($loc, $auth_token);
     $doc = json_decode($doc);
     if(is_null($doc)){
          $message = esc_html(__('no document', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return;
     }
     $path = plugin_dir_path(__DIR__).'asset'.DIRECTORY_SEPARATOR;
     switch($type){
          case 'form':
               $path.= 'typeform_survey.json';
               break;
          case 'result':
               $path.= 'typeform_survey_result.json';
               break;
     }
     @file_put_contents($path, json_encode($doc));
     $message = is_null($doc->description) ? 'survey descriptor is written to: '.$path : $doc->description;
     echo json_encode(
          array(
               'res'=>'success', 
               'doc'=>$doc, 
               'message'=>$message,
               'url'=>$url
          )
     );
};

// todo: surveys is a post with taxonomy
// diss for debug resons is...
add_action('admin_post_exec_get_typeform_surveys', 'exec_get_typeform_surveys');
function exec_get_typeform_surveys(){
     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     $coll = get_typeform_surveys();
     $message = esc_html(__('edit', 'nosuch'));
     echo json_encode(['res'=>'success', 'message'=>$message, 'coll'=>$coll]);
}

