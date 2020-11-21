<?php defined('ABSPATH') || exit;

add_action('init', 'init_toc_utils');
function init_toc_utils(){
     $res = register_post_type(
          'surveyprint_toc',  [
               'label'                  =>'SurveyPrint ToC',
               'description'            =>'SurveyPrint ToC',
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
}

function init_toc($conf){
     $res = wp_insert_post($conf);
     return $res;
}

function get_toc_by_book_id($book_id){
     $author_id = esc_sql(get_author_id());
     $book_id = esc_sql($book_id);
     $sql = <<<EOD
     select * from wp_posts 
          where post_type = 'surveyprint_toc'
               and post_author = '{$author_id}'
               and post_parent = '{$book_id}'
               order by ID 
               desc limit 1;
EOD;
     global $wpdb;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}

function get_toc_by_id($toc_id){
     $book_id = esc_sql($toc_id);
     $author_id = esc_sql(get_author_id());
     $sql = <<<EOD

     select * from wp_posts 
          where post_type = 'surveyprint_toc' 
               and post_author = '{$author_id}' 
               and ID = '{$toc_id}' 
               order by ID 
               desc limit 1;
EOD;
     global $wpdb;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}

function save_toc($conf){
     $res = wp_insert_post($conf);
     return $res;
}

function get_toc_by_thread_id($thread_id, $client_id=null){
     $thread_id = esc_sql($thread_id);
     $author_id = esc_sql(get_author_id());
     if(!is_null($client_id)){ $author_id = esc_sql($client_id); }
     $sql = <<<EOD
          select * from wp_posts 
               where post_type = 'surveyprint_toc'
               and post_author = '{$author_id}'
               and post_parent = '{$thread_id}'
               order by ID 
               desc limit 1;
EOD;
     global $wpdb;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}

function get_toc_by_survey_id($survey_id){
     $sql = <<<EOD
          select * from wp_posts 
               where post_type = 'surveyprint_toc' 
               and post_parent = '{$survey_id}' 
               order by ID 
               desc limit 1;
EOD;
     global $wpdb;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
}
