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

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
     select {$prefix}posts.* from {$prefix}posts 
          where post_type = 'surveyprint_toc'
               and post_author = '{$author_id}'
               and post_parent = '{$book_id}'
               order by ID 
               desc limit 1;
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
/*
     $conf = [
          'post_type'=>'surveyprint_toc',
          'post_author'=>$author_id,
          'post_parent'=>$book_id,
          'orderby'=>'id',
          'order'=>'desc',
          'posts_per_page'=>1
     ];
     $res = query_posts($conf);
     return $res;
*/
}

function get_toc_by_id($toc_id){

     $book_id = esc_sql($toc_id);
     $author_id = esc_sql(get_author_id());

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
     select {$prefix}posts.* from {$prefix}posts 
          where post_type = 'surveyprint_toc' 
               and post_author = '{$author_id}' 
               and ID = '{$toc_id}' 
               order by ID 
               desc limit 1;
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
/*
     $conf = [
          'post_type'=>'surveyprint_toc',
          'post_author'=>$author_id,
          'ID'=>$toc_id,
          'orderby'=>'id',
          'order'=>'desc',
          'posts_per_page'=>1
     ];
     $res = query_posts($conf);
     return $res;
*/
}

function save_toc($conf){
     $res = wp_insert_post($conf);
     return $res;
}

function get_toc_by_survey_id($survey_id){

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
          select {$prefix}posts.* from {$prefix}posts 
               where post_type = 'surveyprint_toc' 
               and post_parent = '{$survey_id}' 
               order by ID 
               desc limit 1;
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
/*
     $conf = [
          'post_type'=>'surveyprint_toc',
          'post_parent'=>$survey_id,
          'orderby'=>'id',
          'order'=>'desc',
          'posts_per_page'=>1
     ];
     $res = query_posts($conf);
     return $res;
*/
}

function write_tree($toc, $link, $title){

     switch($link){

          case 'root':
// some fields are not in groups but in the root of the documen
               $toc[] = [ 
                    'title'=>$title,
                    'group'=>[] 
               ];
               break;

          default:
               $toc = write_branch($toc, $link, $title);
               break;
     }

     return $toc;
}

function write_branch($branch, $link, $title){

     for($idx = 0; $idx < count($branch); $idx++){

          if($link == $branch[$idx]['title']){

               $branch[$idx]['group'][] = [
                    'title'=>$title,
                    'group'=>[]
               ];
          }
          else if(!empty($branch[$idx]['group'])){

               $branch[$idx]['group'] = write_branch($branch[$idx]['group'], $link, $title);
          }
     }

     return $branch;
}

function flatten_toc_refs($toc, $res=null){

     if(null == $res){
          $res = []; 
     }

     if(null == $toc){ 
          return $res; 
     }

     foreach($toc as $node){

          if(!empty($node['group'])){
               $res = flatten_toc_refs($node['group'], $res);
          }
          else {
// no groups in the collection of flatten refs
               $res[] = $node['title'];
          }
     }

     return $res;
}
