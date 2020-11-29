<?php defined('ABSPATH') || exit;

add_action('init', 'init_log_utils');
function init_log_utils(){
      $res = register_post_type(
          'surveyprint_log',  [
               'label'                  =>'SurveyPrint Log',
               'description'            =>'SurveyPrint Log',
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
}

// select post_author, post_date, from_base64(post_content) from wp_posts where post_type = 'surveyprint_log';
function init_log($title, $log){
     if(is_null($log)){ return false; }
     $surveyprint_uuid = psuuid();
     $author_id = esc_sql(get_author_id());
     if(is_null($title)){ $title = surveyprint_uuid; }
     $title = esc_sql($title);
     $log = pigpack($log);
     $conf = [
          'post_type'=>'surveyprint_log',
          'post_author'=>$author_id,
          'post_excerpt'=>$surveyprint_uuid,
          'post_name'=>$surveyprint_uuid,
          'post_title'=>$title,
          'post_content'=>$log
     ];
     $res = wp_insert_post($conf);
     return true;
}
