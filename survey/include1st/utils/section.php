<?php defined('ABSPATH') || exit;

add_action('init', 'init_section_utils');
function init_section_utils(){

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

function init_section($conf){
     $res = wp_insert_post($conf);
     return $res;
}

function get_sections_by_thread_id($thread_id, $client_id=null){

     $thread_id = esc_sql($thread_id);
     $author_id = esc_sql(get_author_id());

     if(!is_null($client_id)){ 
          $author_id = esc_sql($client_id); 
     };

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
          select {$prefix}posts.* from {$prefix}posts 
               where post_type = 'surveyprint_section' 
               and post_author = '{$author_id}'
               and post_parent = '{$thread_id}'
               order by ID
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;

     $conf = [
          'post_type'=>'surveyprint_section',
          'post_author'=>$author_id,
          'post_parent'=>$thread_id,
          'orderby'=>'id',
          'order'=>'desc',
          'posts_per_page'=>-1
     ];

     $res = query_posts($conf);

     return $res;
}

function get_section_by_id($thread_id, $section_id){

     $thread_id = esc_sql($thread_id);
     $section_id = esc_sql($section_id);
     $author_id = esc_sql(get_author_id());

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
          select {$prefix}posts.* from {$prefix}posts 
               where post_type = 'surveyprint_section' 
               and post_author = '{$author_id}' 
               and post_parent = '{$thread_id}' 
               and  ID = '{$section_id}'
               limit 1
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;

     $conf = [
          'post_type'=>'surveyprint_section',
          'post_author'=>$author_id,
          'post_parent'=>$thread_id,
          'ID'=>$section_id,
          'orderby'=>'id',
          'order'=>'desc',
          'posts_per_page'=>1
     ];

     $res = query_posts($conf);

     return $res;
}

function get_section_by_ref($thread_id, $section_ref){

     $thread_id = esc_sql($thread_id);
     $section_id = esc_sql($section_ref);
     $author_id = esc_sql(get_author_id());

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
          select {$prefix}posts.* from {$prefix}posts 
               where post_type = 'surveyprint_section' 
               and post_author = '{$author_id}' 
               and post_excerpt = '{$section_ref}'
               and post_parent = '{$thread_id}'
               limit 1
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;
/*
     $conf = [
          'post_type'=>'surveyprint_section',
          'post_author'=>$author_id,
          'post_parent'=>$thread_id,
          'post_excerpt'=>$section_ref,
          'orderby'=>'id',
          'order'=>'desc',
          'posts_per_page'=>1
     ];
     $res = query_posts($conf);
     return $res;
*/
}

function init_section_from_survey_ref($thread_id, $survey_ref){

// eval of the ids
     $thread_id = esc_sql($thread_id);
     $survey_ref = esc_sql($survey_ref);

// survey
     $survey = get_survey_by_ref($survey_ref)[0];
     if(is_null($survey)){ return null; }

// toc
     $toc = get_toc_by_survey_id($survey->ID)[0];
     if(is_null($toc)){ return null; }

// post of he section
     $post = [];
     $post['survey'] = pagpick($survey->post_content);
     $post['toc'] = pagpick($toc->post_content);
     $post = pigpack($post);
     $conf = [
          'post_type'=>'surveyprint_section',
          'post_author'=>$author_id,
          'post_title'=>$unique_quest,
          'post_excerpt'=>$survey->post_excerpt,
          'post_name'=>$surveyprint_uuid,
          'post_parent'=>$thread_id,
          'post_content'=>$post
     ];
// res
     $section_id = init_section($conf);
     return $section_id;
}

// todo: this is ugly...
function init_section_from_survey_id($thread_id, $survey_id){

// eval of the ids
     $thread_id = esc_sql($thread_id);
     $survey_id = esc_sql($survey_id);

// survey
     $survey = get_survey_by_id($survey_id)[0];

     if(is_null($survey)){ 
          return null; 
     }

// toc
     $toc = get_toc_by_survey_id($survey->ID)[0];
     if(is_null($toc)){ 
          return null; 
     }

// post of he section
     $post = [];
     $post['survey'] = pagpick($survey->post_content);
     $post['toc'] = pagpick($toc->post_content);
     $post = pigpack($post);
     $conf = [
          'post_type'=>'surveyprint_section',
          'post_author'=>$author_id,
          'post_title'=>$unique_quest,
          'post_excerpt'=>$survey->post_excerpt,
          'post_name'=>$surveyprint_uuid,
          'post_parent'=>$thread_id,
          'post_content'=>$post
     ];

// res
     $section_id = init_section($conf);
     return $section_id;
}
