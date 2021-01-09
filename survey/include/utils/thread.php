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

function init_section($conf){
     $res = wp_insert_post($conf);
     return $res;
}

function get_sections_by_thread_id($thread_id, $client_id=null){

     $thread_id = esc_sql($thread_id);
     $author_id = esc_sql(get_author_id());
     if(!is_null($client_id)){ $author_id = esc_sql($client_id); };

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

function init_section_from_survey($thread_id, $survey_ref){
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

function init_panels_from_survey($section_id, $survey_id){

     $section_id = esc_sql($section_id);
     $survey_id = esc_sql($survey_id);

     $questions = get_questions_by_survey_id($survey_id);

     $coll = [];
     foreach($questions as $question){
          $surveyprint_uuid = psuuid();
          $conf = [
               'post_type'=>'surveyprint_panel',
               'post_author'=>$author_id,
               'post_title'=>$question->post_title,
               'post_excerpt'=>$question->post_excerpt,
               'post_name'=>$surveyprint_uuid,
               'post_content'=>$question->post_content,
               'post_parent'=>$section_id
         ];
         $panel_id = init_panel($conf);
         $coll[]= $panel_id;
     }

     return $coll;
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
          delete from wp_posts where id = '{$post_id}' and post_author = '{$client_id}'
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
               delete from wp_posts where id = '{$post_id}' and post_author = '{$client_id}'
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
               delete from wp_posts where id = '{$post_id}' and post_author = '{$client_id}'
EOD;
          $sql = debug_sql($sql);
          $res = $wpdb->query($sql);

          $section_id = $panel->post_parent;
          $panel_ref = $panel->post_excerpt;
          $assets = get_assets_by_panel_ref($section_id, $panel_ref, $limit=10, $client_id=null);
          foreach($assets as $asset){
               $sql = <<<EOD
                    delete from wp_posts where id = '{$post_id}' and post_author = '{$client_id}'
EOD;
               $sql = debug_sql($sql);
               $res = $wpdb->query($sql);
          }

          return true;
     }
}
