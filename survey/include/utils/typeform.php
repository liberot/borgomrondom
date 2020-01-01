<?php defined('ABSPATH') || exit;

function get_typeform_surveys(){
     $author_id = esc_sql(get_author_id());
     global $wpdb;
     $sql = <<<EOD
          select * from wp_posts where post_type = 'surveyprint_survey' and post_excerpt = 'typeform' order by ID desc;
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}

function get_typeform_surveys_by_ref($ref){

     $ref = esc_sql($ref);
     global $wpdb;
     $sql = <<<EOD
          select * from wp_posts where post_type = 'surveyprint_survey' and post_excerpt = 'typeform' and post_name = '{$ref}' order by ID desc;
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}

function init_typeform_survey($survey_file_name){

     $path = sprintf('%s/%s', Path::get_typeform_dir(), $survey_file_name);
     $data = @file_get_contents($path);
     if(is_null($data)){ return false; }
     $doc = json_decode($data);
     if(is_null($doc)){ return false; }
     $doc = walk_the_doc($doc);

// checks whether or not survey is already stored
     // $survey_title = $doc['title'];
     // $survey = get_survey_by_title($survey_title)[0];
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
     $survey_id = wp_insert_post([
          'post_type'=>'surveyprint_survey',
          'post_title'=>$survey_title,
          'post_name'=>$survey_type,
          'post_excerpt'=>$survey_ref,
          'post_content'=>pigpack($doc)
     ]);
     if(is_null($survey_id)){ return false; }

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

     if(is_null($toc_id)){ return false; }

// parses the next caption
     $redirect = $doc['settings']['redirect_after_submit_url'];
     preg_match('/\/to\/(.{0,124})#/', $redirect, $mtch);
     if(!empty($mtch)){ if(!is_null($mtch[1])){
          parse_next_section_of_survey($mtch[1]);
     }}
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

