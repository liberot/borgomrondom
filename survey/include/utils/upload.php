<?php defined('ABSPATH') || exit;

add_action('init', 'init_upload_utils');
function init_upload_utils(){

     $res = register_post_type(
          'surveyprint_asset',  [
               'label'                  =>'SurveyPrint Asset',
               'description'            =>'SurveyPrint Asset',
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

function init_asset($conf){
     $res = wp_insert_post($conf);
     return $res;
}

function get_asset_by_id($asset_id){

     $asset_id = esc_sql($survey_id);
     $author_id = esc_sql(get_author_id());

     $res = query_posts([
          'post_type'=>'surveyprint_asset',
          'post_author'=>$author_id,
          'ID'=>$asset_id
     ]);

     return $res;
}

function get_assets_by_panel_ref($section_id, $panel_ref, $limit=10, $client_id=null){

     $section_id = esc_sql($section_id);
     $panel_ref = esc_sql($panel_ref);
     $limit = esc_sql($limit);
     $author_id = esc_sql(get_author_id());

     if(!is_null($client_id)){ $author_id = esc_sql($client_id); }

     global $wpdb;
     $sql = <<<EOD
          select wp_posts.* from wp_posts 
               where post_type = 'surveyprint_asset' 
               and post_author = '{$author_id}' 
               and post_parent = '{$section_id}' 
               and post_excerpt = '{$panel_ref}' 
               order by ID desc
               limit {$limit};
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
/*
     $conf = [
          'post_type'=>'surveyprint_asset',
          'post_author'=>$author_id,
          'post_parent'=>$section_id,
          'post_excerpt'=>$panel_ref,
          'orderby'=>'id',
          'order'=>'desc',
          'posts_per_page'=>$limit
     ];
     $res = query_posts($conf);
     return $res;
*/
}


