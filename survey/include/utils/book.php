<?php defined('ABSPATH') || exit;

add_action('init', 'init_book_utils');
function init_book_utils(){
     $res = register_post_type(
          'surveyprint_book',  [
               'label'                  =>'SurveyPrint Book',
               'description'            =>'SurveyPrint Book',
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
     $res = register_post_type(
          'surveyprint_chapter',  [
               'label'                  =>'SurveyPrint Chapter',
               'description'            =>'SurveyPrint Chapter',
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
     $res = register_post_type(
          'surveyprint_spread',  [
               'label'                  =>'SurveyPrint Spread',
               'description'            =>'SurveyPrint Spread',
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

function init_book($conf){
     $res = wp_insert_post($conf);
     return $res;
}

function init_chapter($conf){
     $res = wp_insert_post($conf);
     return $res;
}

function init_spread($conf){
     $res = wp_insert_post($conf);
     return $res;
}

function get_books(){
     $res = query_posts([
          'post_type'=>'surveyprint_book'
     ]);
     return $res;
}

function get_book_by_id($book_id){
     $book_id = esc_sql($book_id);
     $author_id = esc_sql(get_author_id());
$sql = <<<EOD
          select * from wp_posts where post_type = 'surveyprint_book' and post_author = '{$author_id}' and ID = '{$book_id}';
EOD;
     $sql = debug_sql($sql);
     global $wpdb;
     $res = $wpdb->get_results($sql);
     return $res;
}

function get_chapter_by_book_id($book_id){
     $book_id = esc_sql($book_id);
     $author_id = esc_sql(get_author_id());
     $sql = <<<EOD
          select * from wp_posts where post_type = 'surveyprint_chapter' and post_author = '{$author_id}' and post_parent = '{$book_id}' order by ID desc; 
EOD;
     $sql = debug_sql($sql);
     global $wpdb;
     $res = $wpdb->get_results($sql);
     return $res;
}

function get_spreads_by_chapter_id($chapter_id){
     $chapter_id = esc_sql($chapter_id);
     $author_id = esc_sql(get_author_id());
     $sql = <<<EOD
          select * from wp_posts where post_type = 'surveyprint_spread' and post_author = '{$author_id}' and post_parent = '{$chapter_id}' order by ID desc;
EOD;
     $sql = debug_sql($sql);
     global $wpdb;
     $res = $wpdb->get_results($sql);
     return $res;
}


