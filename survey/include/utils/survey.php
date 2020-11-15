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
               'taxonomies'             => array('ategory', 'post_tag'),
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
     global $wpdb;
     $sql = <<<EOD
          select * from wp_posts where post_type = 'surveyprint_survey' order by ID desc;
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}


function get_survey_by_id($survey_id){
     $survey_id = esc_sql($survey_id);
     $author_id = esc_sql(get_author_id());
     $sql = <<<EOD
          select * from wp_posts where post_type = 'surveyprint_survey' and ID = '{$survey_id}'
EOD;
     $sql = debug_sql($sql);
     global $wpdb;
     $res = $wpdb->get_results($sql);
     return $res;
}

function get_questions_by_survey_id($survey_id){
     $survey_id = esc_sql($survey_id);
     $sql = <<<EOD
          select * from wp_posts where post_type = 'surveyprint_question' and post_parent = '{$survey_id}' order by ID;
EOD;
     $sql = debug_sql($sql);
     global $wpdb;
     $res = $wpdb->get_results($sql);
     return $res;
}

function get_surveys_by_ref($ref){

    $ref = esc_sql($ref);
     $sql = <<<EOD
          select * from wp_posts where post_type = 'surveyprint_survey' and post_excerpt = '{$ref}' order by ID desc;
EOD;
     $sql = debug_sql($sql);
     global $wpdb;
     $res = $wpdb->get_results($sql);
     return $res;
}

function get_question_by_id($id){
     $sql = <<<EOD
          select * from wp_posts where post_type = 'surveyprint_question' and ID = '{$id}' order by ID desc limit 1;
EOD;
     $sql = debug_sql($sql);
     global $wpdb;
     $res = $wpdb->get_results($sql);
     return $res;
}

function get_survey_by_title($title){
     $title = esc_sql($title);
     $sql = <<<EOD
          select * from wp_posts where post_type = 'surveyprint_survey' and post_title = '{$title}' order by ID desc limit 1;
EOD;
     $sql = debug_sql($sql);
     global $wpdb;
     $res = $wpdb->get_results($sql);
     return $res;
}

