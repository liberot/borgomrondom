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

// policy
     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $survey_type = 'typeform'; 

// sets file name that is to be parsed
     $survey_file_name = trim_incoming_filename($_POST['survey_file_name']);
     if(is_null($survey_file_name)){
          $survey_file_name = 'typeform_survey.json';
     }

// reads tpeform survey json
     $path = sprintf('%s/%s', Path::get_typeform_dir(), $survey_file_name);
     $data = @file_get_contents($path);
     if(is_null($data)){
          $message = esc_html(__('no json', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message, 'path'=>$path));
          return false;
     }

// parses json document
     $doc = json_decode($data);
     if(is_null($doc)){
          $message = esc_html(__('no document: ', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message.$path));
          return false;
     }
     $doc = walk_the_doc($doc);

// inserts a post of type survey
     $survey_type = 'typeform'; 
     $survey_ref = $doc['id'];
     $survey_title = $doc['title'];
     $surveyprint_uuid = psuuid();
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
          return false;
     }

// inserts posts of type questions and groups of questions into the db
     $nodes = insert_question_groups($doc['fields'], $survey_id);

// inserts post type table of contents
     $post_content = [];
     $post_content['master'] = $nodes;
     $post_content['rulez'] = $doc['logic'];
     $post_content['refs'] = flatten_toc_refs($nodes);
     $post_content = pigpack($post_content);
     $conf = [
          'post_type'=>'surveyprint_toc',
          'post_title'=>$survey_title,
          'post_name'=>$survey_type,
          'post_excerpt'=>$survey_ref,
          'post_parent'=>$survey_id,
          'post_content'=>$post_content
     ];
     $toc_id = init_toc($conf);

     if(is_null($toc_id)){
          $message = esc_html(__('no toc write', 'nosuch'));
          echo json_encode([ 'res'=>'failed', 'message'=>$message ]);
          return;
     }

     $message = sprintf('survey added: %s: %s', $survey_id, $survey_title);
     echo json_encode(array('res'=>'success', 'message'=>$message, 'nodes'=>$nodes));
}

function insert_question_groups($nodes, $survey_id, $link=null, $res=null){

     if(is_null($res)){ 
          $res = []; 
     }

     if(is_null($link)){ 
          $link = 'root'; 
     }

     if(is_null($nodes)){ 
          return $res; 
     }

     foreach($nodes as $node){
// writes toc reference of the insert
          $res = insert_into_toc($res, $link, $node['ref']);
// writes a post of type question
          $node['conf'] = [];
          $node['conf']['max_asset'] = '1';
          $node['conf']['layout_group'] = 'default';
          $surveyprint_uuid = psuuid();
          $conf = [ 
               'post_type'=>'surveyprint_question',
               'post_title'=>$node['title'],
               'post_excerpt'=>$node['ref'],
               'post_name'=>$surveyprint_uuid,
               'post_parent'=>$survey_id,
               'post_content'=>pigpack($node)
          ];
          $question_id = wp_insert_post($conf);
          if(!is_null($node['properties']['fields'])){
// writes all groups of groups of groups
               $res = insert_question_groups($node['properties']['fields'], $survey_id, $node['ref'], $res);
               continue;
          }
     }

     return $res;
}

add_action('admin_post_exec_download_typeform_survey', 'exec_download_typeform_survey');
function exec_download_typeform_survey(){

     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return;
     }

     $typeform_base = 'https://api.typeform.com/forms';

     $auth_token = trim_incoming_string($_POST['auth_token']);
     $bucket = trim_incoming_filename($_POST['bucket']);
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

     $path = '/tmp/delete_me.json';
     switch($type){
          case 'form':
               $path = sprintf('%s/%s', Path::get_typeform_dir(), 'typeform_survey.json');
               break;
          case 'result':
               $path = sprintf('%s/%s', Path::get_typeform_dir(), 'typeform_survey_result.json');
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

