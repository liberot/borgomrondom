<?php defined('ABSPATH') || exit;

add_action('init', 'init_thread_utils');
function init_thread_utils(){
     $res = register_post_type(
          'surveyprint_thread',  [
               'label'                  =>'SurveyPrint Thread',
               'description'            =>'SurveyPrint Thread',
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
               'has_archive'            => false,
               'taxonomies'             => array('category', 'post_tag'),
               'show_in_rest'           => false
          ]
     );
     $res = register_post_type(
          'surveyprint_section',  [
               'label'                  =>'SurveyPrint Section',
               'description'            =>'SurveyPrint Section',
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
               'has_archive'            => false,
               'taxonomies'             => array('category', 'post_tag'),
               'show_in_rest'           => false
          ]
     );
     return $res;
}

function init_thread($conf){
     $res = wp_insert_post($conf);
     return $res;
}

function get_threads_of_client(){
     $author_id = esc_sql(get_author_id());
     $res = query_posts([
          'post_type'=>'surveyprint_thread',
          'post_author'=>$author_id
     ]);
     return $res;
}

function get_threads_by_survey_id($survey_id){
     $author_id = esc_sql(get_author_id());
     $survey_id = esc_sql($survey_id);
     $sql = <<<EOD
          select * from wp_posts where post_type = 'surveyprint_thread' and post_author = '{$author_id}' and post_parent = '{$survey_id}' order by ID desc;
EOD;
     $sql = debug_sql($sql);
     global $wpdb;
     $res = $wpdb->get_results($sql);
     return $res;
}

function get_thread_by_id($thread_id){
     $author_id = esc_sql(get_author_id());
     $thread_id = esc_sql($thread_id);
     $sql = <<<EOD
          select * from wp_posts where post_type = 'surveyprint_thread' and post_author = '{$author_id}' and ID = '{$thread_id}';
EOD;
     $sql = debug_sql($sql);
     global $wpdb;
     $res = $wpdb->get_results($sql);
     return $res;
}

function get_threads(){
     $sql = <<<EOD
          select * from wp_posts where post_type = 'surveyprint_thread' order by ID desc;
EOD;
     $sql = debug_sql($sql);
     global $wpdb;
     $res = $wpdb->get_results($sql);
     return $res;
}

function init_section($conf){
     $res = wp_insert_post($conf);
     return $res;
}

function get_sections_by_thread_id($thread_id){
     $thread_id = esc_sql($thread_id);
     $author_id = esc_sql(get_author_id());
     $sql = <<<EOD
          select * from wp_posts where post_type = 'surveyprint_section' and post_author = '{$author_id}' and post_parent = '{$thread_id}';
EOD;
     $sql = debug_sql($sql);
     global $wpdb;
     $res = $wpdb->get_results($sql);
     return $res;
}

function get_section_by_id($section_id){
     $section_id = esc_sql($section_id);
     $author_id = esc_sql(get_author_id());
     $sql = <<<EOD
          select * from wp_posts where post_type = 'surveyprint_section' and post_author = '{$author_id}' and  ID = '{$section_id}';
EOD;
     $sql = debug_sql($sql);
     global $wpdb;
     $res = $wpdb->get_results($sql);
     return $res;
}
