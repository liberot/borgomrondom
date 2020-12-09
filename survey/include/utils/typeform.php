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
     if(is_null($data)){
          return false;
     }

     $doc = json_decode($data);

     if(is_null($doc)){
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
          return false;
     }
}
