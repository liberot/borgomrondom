<?php defined('ABSPATH') || exit;



function bb_init_log_utils(){

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
               'taxonomies'             => array('post_tag'),
               'show_in_rest'           => false
          ]
     );
}



function bb_init_log($title, $log){

     if(is_null($log)){ 
          return false; 
     }

     $surveyprint_uuid = bb_get_psuuid();
     $author_id = esc_sql(bb_get_author_id());

     if(is_null($title)){ 
          $title = surveyprint_uuid; 
     }

     $title = esc_sql($title);
     $log = serialize($log);

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



