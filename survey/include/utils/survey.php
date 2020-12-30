<?php defined('ABSPATH') || exit;

add_action('init', 'init_survey_utils');
function init_survey_utils(){

      $res = register_post_type(

          'surveyprint_survey',  [
               'label'                  =>'SurveyPrint Survey',
               'description'            =>'SurveyPrint Survey',
               'public'                 => false,
               'hierarchical'           => true,
               'exclude_from_search'    => true,
               'publicly_queryable'     => false,
               'show_ui'                => false,
               'show_in_menu'           => false,
               'show_in_nav_menus'      => false,
               'query_var'              => false,
               'rewrite'                => false,
               'capability_type'        => 'post',
               'has_archive'            => false,
               'taxonomies'             => array('category', 'post_tag'),
               'show_in_rest'           => false
          ]
     );

     $res = register_post_type(

          'surveyprint_question',  [
               'label'                  =>'SurveyPrint Question',
               'description'            =>'SurveyPrint Question',
               'public'                 => false,
               'hierarchical'           => true,
               'exclude_from_search'    => true,
               'publicly_queryable'     => false,
               'show_ui'                => false,
               'show_in_menu'           => false,
               'show_in_nav_menus'      => false,
               'query_var'              => true,
               'rewrite'                => false,
               'capability_type'        => 'post',
               'has_archive'            => true,
               'taxonomies'             => array('category', 'post_tag'),
               'show_in_rest'           => false
          ]
     );

     return $res;
}

function init_survey($conf){
     $res = wp_insert_post($conf);
     return $res;
}

function get_surveys(){

/*
     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select {$prefix}posts.* from {$prefix}posts where post_type = 'surveyprint_survey' order by ID desc;
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
*/

     $conf = [
          'post_type'=>'surveyprint_survey',
          'posts_per_page'=>-1
     ];
     $res = query_posts($conf);
     return $res;
}


function get_survey_by_id($survey_id){

     $survey_id = esc_sql($survey_id);
     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
          select {$prefix}posts.* from {$prefix}posts where post_type = 'surveyprint_survey' and ID = '{$survey_id}'
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;

/*
     $conf = [
          'post_type'=>'surveyprint_survey',
          'ID'=>$survey_id,
          'posts_per_page'=>-1
     ];
     $res = query_posts($conf);
     return $res;
*/
}

function get_questions_by_survey_id($survey_id){
     $survey_id = esc_sql($survey_id);

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
          select {$prefix}posts.* from {$prefix}posts where post_type = 'surveyprint_question' and post_parent = '{$survey_id}' order by ID;
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);

     return $res;

/*
     $conf = [
          'post_type'=>'surveyprint_question',
          'post_parent'=>$survey_id,
          'orderby'=>'id',
          'order'=>'desc',
          'posts_per_page'=>-1
     ];
     $res = query_posts($conf);
     return $res;
*/
}

function get_question_by_id($id){
     $id = esc_sql($id);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select {$prefix}posts.* from {$prefix}posts where post_type = 'surveyprint_question' and ID = '{$id}' order by ID desc limit 1;
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
/*
     $conf = [
          'post_type'=>'surveyprint_question',
          'ID'=>$id,
          'orderby'=>'ID',
          'posts_per_page'=>1
     ];
     $res = query_posts($conf);
     return $res;
*/
}

function get_question_by_ref($survey_id, $panel_ref){

     $survey_id = esc_sql($survey_id);
     $panel_ref = esc_sql($panel_ref);

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
          select {$prefix}posts.* 
               from {$prefix}posts 
               where post_type = 'surveyprint_question' 
               and post_excerpt = '{$panel_ref}' 
               and post_parent = '{$survey_id}'
               order by ID desc limit 1;
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;

/*
     $conf = [
          'post_type'=>'surveyprint_question',
          'post_excerpt'=>$panel_ref,
          'post_parent'=>$survey_id,
          'orderby'=>'ID',
          'order'=>'DESC',
          'posts_per_page'=>1
     ];
     $res = query_posts($conf);
     return $res;
*/
}

function get_survey_by_title($title){
     $title = esc_sql($title);

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select {$prefix}posts.* from {$prefix}posts where post_type = 'surveyprint_survey' and post_title = '{$title}' order by ID desc limit 1;
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
/*
     $conf = [
          'post_type'=>'surveyprint_survey',
          'post_title'=>$title,
          'orderby'=>'ID',
          'order'=>'DESC',
          'posts_per_page'=>1
     ];
     $res = query_posts($conf);
     return $res;
*/
}

function get_survey_by_ref($ref){
     $ref = esc_sql($ref);

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
          select {$prefix}posts.* from {$prefix}posts where post_type = 'surveyprint_survey' and post_excerpt = '{$ref}' order by ID desc limit 1;
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;

/*
     $conf = [
          'post_type'=>'surveyprint_survey',
          'post_excerpt'=>$ref,
          'orderby'=>'id',
          'order'=>'desc',
          'posts_per_page'=>1
     ];
     $res = query_posts($conf);
     return $res;
*/
}

