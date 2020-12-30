<?php defined('ABSPATH') || exit;

add_action('init', 'init_panel_utils');
function init_panel_utils(){

     $res = register_post_type(
          'surveyprint_panel',  [
               'label'                  =>'SurveyPrint Panel',
               'description'            =>'SurveyPrint Panel',
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

function init_panel($conf){
     $res = wp_insert_post($conf);
     return $res;
}

function get_panel_by_id($panel_id){

     $author_id = esc_sql(get_author_id());
     $panel_id = esc_sql($panel_id);

     $sql = <<<EOD
          select wp_posts.* from wp_posts where post_type = 'surveyprint_panel' and post_author = '{$author_id}' and ID = '{$panel_id}' order by ID desc;
EOD;
     $sql = debug_sql($sql);
     global $wpdb;
     $res = $wpdb->get_results($sql);
     return $res;
/*
     $conf = [
          'post_type'=>'surveyprint_panel',
          'post_author'=>$author_id,
          'ID'=>$panel_id,
          'orderby'=>'id',
          'order'=>'desc',
          'posts_per_page'=>-1
     ];
     $res = query_posts($conf);
     return $res;
*/
}

function get_panels_by_thread_id($thread_id){

     $author_id = esc_sql(get_author_id());
     $thread_id = esc_sql($thread_id);

     global $wpdb;
     $sql = <<<EOD
          select wp_posts.* from wp_posts where post_type = 'surveyprint_panel' and post_author = '{$author_id}' and post_parent = '{$thread_id}' 
          order by ID desc;
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);

/*
     $conf = [
          'post_type'=>'surveyprint_panel',
          'post_author'=>$author_id,
          'post_parent'=>$thread_id,
          'orderby'=>'id',
          'order'=>'desc',
          'posts_per_page'=>-1
     ];
     $res = query_posts($conf);
     return $res;
*/
}

function get_panel_by_ref($section_id, $panel_ref, $client_id=null){

     $section_id = esc_sql($section_id);
     $panel_ref = esc_sql($panel_ref);
     $author_id = esc_sql(get_author_id());
     if(!is_null($client_id)){ $author_id = esc_sql($client_id); }

     global $wpdb;
     $sql = <<<EOD
          select wp_posts.* from wp_posts where post_type = 'surveyprint_panel' and post_author = '{$author_id}' and post_parent = '{$section_id}' 
          and post_excerpt = '{$panel_ref}'
          order by ID desc
          limit 1
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
/*
     $conf = [
          'post_type'=>'surveyprint_panel',
          'post_author'=>$author_id,
          'post_parent'=>$section_id,
          'orderby'=>'id',
          'order'=>'desc',
          'posts_per_page'=>1
     ];
     $res = query_posts($conf);
     return $res;
*/
}


