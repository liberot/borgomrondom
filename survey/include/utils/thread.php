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

     return $res;
}

function init_thread($conf){
     $res = wp_insert_post($conf);
     return $res;
}

function get_thread_of_client(){
     $author_id = esc_sql(get_author_id());

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
          select {$prefix}posts.* from {$prefix}posts
               where post_type = 'surveyprint_thread'
               and post_author = '{$author_id}'
               order by ID
               limit 1
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
/*
     $conf = [
          'post_type'=>'surveyprint_thread',
          'post_author'=>$author_id,
          'orderby'=>'id',
          'order'=>'desc',
          'posts_per_page'=>1
     ];
     $res = query_posts($conf);
     return $res;
*/
}

function get_thread_by_id($thread_id, $client_id=null){

     $thread_id = esc_sql($thread_id);
     $author_id = esc_sql(get_author_id());

     if(!is_null($client_id)){
          $author_id = esc_sql($client_id);
     }

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
          select {$prefix}posts.* from {$prefix}posts
               where post_type = 'surveyprint_thread' 
               and post_author = '{$author_id}' 
               and ID = '{$thread_id}'
               limit 1;
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
/*
     $conf = [
          'post_type'=>'surveyprint_thread',
          'post_author'=>$author_id,
          'ID'=>$thread_id,
          'orderby'=>'id',
          'order'=>'desc',
          'posts_per_page'=>1
     ];
     $res = query_posts($conf);
     return $res;
*/
}

function get_threads(){

/*
     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select {$prefix}posts.* from {$prefix}posts where post_type = 'surveyprint_thread' order by ID desc;
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
*/

     $conf = [
          'post_type'=>'surveyprint_thread',
          'orderby'=>'id',
          'order'=>'desc',
          'posts_per_page'=>-1
     ];
     $res = query_posts($conf);
     return $res;
}

function delete_thread_by_id($thread_id, $client_id=null){

     $thread_id = esc_sql($thread_id);

     if(null == $client_id){
          $client_id = esc_sql(get_author_id());
     }
     else {
          $client_id = esc_sql($client_id);
     }

     $post = get_thread_by_id($thread_id, $client_id)[0];

     if(null == $post){
          return false;
     }
     $post_id = $post->ID;

     if(null == $post){
          return false;
     }

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
          delete from {$prefix}posts where id = '{$post_id}' and post_author = '{$client_id}'
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->query($sql);

     $sections = get_sections_by_thread_id($thread_id, $client_id);
     if(null == $sections){
         return false;
     }
     
     foreach($sections as $section){
          $post_id = $section->ID;
          $sql = <<<EOD
               delete from {$prefix}posts where id = '{$post_id}' and post_author = '{$client_id}'
EOD;
          $sql = debug_sql($sql);
          $res = $wpdb->query($sql);
     }

     $panels = get_panels_by_thread_id($thread_id, $client_id);
     if(null == $panels){
          return false;
     }

     foreach($panels as $panel){
          $post_id = esc_sql($panel->ID);
          $sql = <<<EOD
               delete from {$prefix}posts where id = '{$post_id}' and post_author = '{$client_id}'
EOD;
          $sql = debug_sql($sql);
          $res = $wpdb->query($sql);

          $section_id = $panel->post_parent;
          $panel_ref = $panel->post_excerpt;
          $assets = get_assets_by_panel_ref($section_id, $panel_ref, $limit=10, $client_id=null);
          foreach($assets as $asset){
               $sql = <<<EOD
                    delete from {$prefix}posts where id = '{$post_id}' and post_author = '{$client_id}'
EOD;
               $sql = debug_sql($sql);
               $res = $wpdb->query($sql);
          }

          return true;
     }
}
