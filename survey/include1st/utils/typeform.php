<?php defined('ABSPATH') || exit;

function get_typeform_surveys(){
     $author_id = esc_sql(get_author_id());

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
          select {$prefix}posts.* from {$prefix}posts where post_type = 'surveyprint_survey' and post_excerpt = 'typeform' order by ID desc;
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
/*
     $conf = [
          'post_type'=>'surveyprint_survey',
          'post_excerpt'=>'typeform',
          'orderby'=>'id',
          'order'=>'desc',
          'posts_per_page'=>-1
     ];
     $res = query_posts($conf);
     return $res;
*/
}

function get_typeform_surveys_by_ref($ref){

     $ref = esc_sql($ref);

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
          select {$prefix}posts.* 
               from {$prefix}posts 
               where post_type = 'surveyprint_survey' 
               and post_excerpt = 'typeform' 
               and post_name = '{$ref}' 
               order by ID desc;
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
/*
     $conf = [
          'post_type'=>'surveyprint_survey',
          'post_excerpt'=>'typeform',
          'post_name'=>$ref,
          'orderby'=>'id',
          'order'=>'desc',
          'posts_per_page'=>-1
     ];
     $res = query_posts($conf);
     return $res;
*/
}

function init_typeform_survey($survey_file_name){

     $path = sprintf('%s/%s', Path::get_typeform_dir(), $survey_file_name);

     $data = @file_get_contents($path);

     if(is_null($data)){ 
          return false; 
     }

     $doc = json_decode($data);

     if(is_null($doc)){ 
          return false; 
     }

     $doc = walk_the_doc($doc);

// evaluates whether or not survey is already stored
     $survey_ref = $doc['id'];
     $survey = get_survey_by_ref($survey_ref)[0];

     if(!is_null($survey)){
          return false;
     }

// inserts a post of type survey
     $survey_type = 'typeform'; 
     $survey_ref = $doc['id'];
     $survey_title = $doc['title'];
     $surveyprint_uuid = psuuid();
     $conf = [
          'post_type'=>'surveyprint_survey',
          'post_title'=>$survey_title,
          'post_name'=>$survey_type,
          'post_excerpt'=>$survey_ref,
          'post_content'=>pigpack($doc)
     ];

     $survey_id = wp_insert_post($conf);

     if(is_null($survey_id)){
          return false;
     }

// inserts posts of type questions and groups of questions into the db
     $nodes = insert_question_groups($doc['fields'], $survey_id);

// inserts post type table of contents
     $post_content = [];
     $post_content['rulez'] = $doc['logic'];
     $post_content['refs'] = flatten_toc_refs($nodes);
     $post_content['master'] = $nodes;
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
          return false; 
     }

// parses the next caption
     $redirect = $doc['settings']['redirect_after_submit_url'];

     preg_match('/\/to\/(.{0,124})#/', $redirect, $mtch);

     if(!empty($mtch)){ if(!is_null($mtch[1])){
          parse_next_section_of_survey($mtch[1]);
     }}

     return true;
}

function parse_next_section_of_survey($survey_ref){

     // checks whether or not survey is already parsed
     $survey = get_survey_by_ref($survey_ref)[0];
     if(!is_null($survey)){ return false; }

     // reads files
     $files = read_typeform_json_descriptors();
     foreach($files as $file){
          if(false !== strpos($file, $survey_ref)){
               init_typeform_survey($file);
               return true;
          }
     }

     return false;
}

function read_typeform_json_descriptors(){
     $files = [];
     $path = Path::get_typeform_dir();
     $h = opendir($path);
     if(is_null($h)){ return $files; }
     while(false !== ($file = readdir($h))){
          if($file != '.' && $file != '..'){
               preg_match('/(.json$)/', $file, $mtch);
               if(!empty($mtch)){
                    $files[]= $file;
               }
          }
     }
     closedir($h);
     return $files;
}

function insert_question_groups($nodes, $survey_id, $parent=null, $res=null){

     if(is_null($res)){ 
          $res = []; 
     }

     if(is_null($parent)){ 
          $parent = 'root'; 
     }

     if(is_null($nodes)){ 
          return $res; 
     }

     foreach($nodes as $node){

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

// writes current node(the current field) into the toc tree
          $res = write_tree($res, $parent, $node['ref']);

// writes all groups of groups of groups
          if(!is_null($node['properties']['fields'])){
               $res = insert_question_groups($node['properties']['fields'], $survey_id, $node['ref'], $res);
               continue;
          }
     }

     return $res;
}

