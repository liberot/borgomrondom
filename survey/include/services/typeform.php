<?php defined('ABSPATH') || exit;

add_action('admin_post_exec_edit_typeform_survey', 'exec_edit_typeform_survey');
function exec_edit_typeform_survey(){

     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     init_log('admin_post_exec_edit_typeform_survey', []);

     $ref = trim_incoming_filename($_POST['bucket']); 
     $coll = get_typeform_surveys_by_ref($ref);

     $message = esc_html(__('edit', 'bookbuilder'));
     echo json_encode(array('res'=>'failed', 'message'=>$message, 'coll'=>$coll));
}

add_action('admin_post_exec_construct_all_surveys', 'exec_construct_all_surveys');
function exec_construct_all_surveys(){

// policy
     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     init_log('admin_post_exec_construct_all_surveys', []);

     $files = read_typeform_json_descriptors();
     foreach($files as $file){
          $res = init_typeform_survey($file);
     }

     $message = sprintf('surveys added %s', '');
     echo json_encode(array('res'=>'success', 'message'=>$message, 'files'=>$files));
}

add_action('admin_post_exec_construct_typeform_survey', 'exec_construct_typeform_survey');
function exec_construct_typeform_survey(){

// policy
     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }


// sets file name that is to be parsed
     $survey_file_name = trim_incoming_filename($_POST['survey_file_name']);
     if(is_null($survey_file_name)){
          $survey_file_name = 'typeform_survey.json';
     }

     init_log('admin_post_exec_construct_typeform_survey', ['survey_file_name'=>$survey_file_name]);

     $res = init_typeform_survey($survey_file_name);
     if(false == $res){
          $message = [];
          $message['survey_file_name'] = sprintf('%s', $survey_file_name);
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $message = sprintf('survey added: %s', $survey_file_name);
     echo json_encode(array('res'=>'success', 'message'=>$message));
     return true;
}

function insert_question_groups($nodes, $survey_id, $parent=null, $res=null){

     if(is_null($res)){ $res = []; }
     if(is_null($parent)){ $parent = 'root'; }
     if(is_null($nodes)){ return $res; }

     foreach($nodes as $node){
// writes toc reference of the insert
          $res = insert_into_toc($res, $parent, $node['ref']);
// writes a post of type question
          $node['conf'] = [];
          $node['conf']['max_asset'] = '1';
          $node['conf']['layout_group'] = 'default';
          $node['conf']['parent'] = $parent;
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
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return;
     }

     $typeform_base = 'https://api.typeform.com/forms';

     $auth_token = trim_incoming_filename($_POST['auth_token']);
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
          $message = esc_html(__('no document: ', 'bookbuilder'));
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
          $message = esc_html(__('policy match', 'bookbuilder'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }
     $coll = get_typeform_surveys();
     $message = esc_html(__('edit', 'bookbuilder'));
     echo json_encode(['res'=>'success', 'message'=>$message, 'coll'=>$coll]);
}

