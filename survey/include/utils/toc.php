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
     select wp_posts.* from wp_posts 
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

     $sql = <<<EOD
     select wp_posts.* from wp_posts 
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

     $sql = <<<EOD
          select wp_posts.* from wp_posts 
               where post_type = 'surveyprint_toc' 
               and post_parent = '{$survey_id}' 
               order by ID 
               desc limit 1;
EOD;
     global $wpdb;
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



// add_action('init', 'test_insert_into_toc');
function test_insert_into_toc(){
     $toc = [];
     $toc = insert_into_toc($toc, 'root', 'a100');
     $toc = insert_into_toc($toc, 'root', 'a111');
     $toc = insert_into_toc($toc, 'a111', 'a109');
     $toc = insert_into_toc($toc, 'a111', 'a110');
     $toc = insert_into_toc($toc, 'a111', 'a310');
     $toc = insert_into_toc($toc, 'a310', 'b310');
     $toc = insert_into_toc($toc, 'a100', 'd310');
     $toc = insert_into_toc($toc, 'a100', 'd510');
     $toc = insert_into_toc($toc, 'a100', 'x510');
     $toc = insert_into_toc($toc, 'x510', 'y510');
     $toc = insert_into_toc($toc, 'y510', 'z510');
     $toc = insert_into_toc($toc, 'y510', 'u510');
     $toc = insert_into_toc($toc, 'y510', 'u410');
     $toc = insert_into_toc($toc, 'y510', 'u310');
     print_r($toc);
     $refs = flatten_toc_refs($toc, []);
     print_r($refs);
     exit();
}

function insert_into_toc($toc, $link, $ref){
     switch($link){
          case 'root':
               $toc[] = [ 
                    'title'=>$ref, 
                    'group'=>[] 
               ];
               break;
          default:
               $toc = insert_into_branch($toc, $link, $ref);
               break;
     }
     return $toc;
}

function insert_into_branch($branch, $link, $ref){
     for($idx = 0; $idx < count($branch); $idx++){
          if($link == $branch[$idx]['title']){
               $branch[$idx]['group'][] = [
                    'title'=>$ref,
                    'group'=>[]
               ];
          }
          else if(!empty($branch[$idx]['group'])){
               $branch[$idx]['group'] = insert_into_branch($branch[$idx]['group'], $link, $ref);
          }
     }
     return $branch;
}

function flatten_toc_refs($toc, $res=null){
     if(null == $res){ $res = []; }
     if(null == $toc){ return $res; }
     foreach($toc as $node){
          $res[] = $node['title'];
          if(!empty($node['group'])){
               $res = flatten_toc_refs($node['group'], $res);
          }
     }
     return $res;
}
