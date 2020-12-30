<?php defined('ABSPATH') || exit;

function clean_layouts(){

     global $wpdb;
     $tables = [
          'surveyprint_layout',
     ];

     $res = null;
     foreach($tables as $table){
          $sql = <<<EOD
               delete from wp_posts where post_type = '{$table}'
EOD;
          $sql = debug_sql($sql);
          $res = $wpdb->get_results($sql);
     }
     return $res;
/*
     foreach($tables as $table){
          $conf = [
               'post_type'=>$table,
               'posts_per_page'=>-1
          ];
          $res = query_posts($conf);
          foreach($res as $post){
               $res = wp_delete_post($post->ID, true);
          }
     }
     return $res;
*/
}

function clean_surveys(){

     $tables = [
          'surveyprint_question',
          'surveyprint_section',
          'surveyprint_thread'
     ];

     global $wpdb;
     $res = null;
     foreach($tables as $table){

          $sql = <<<EOD
               delete from wp_posts where post_type = '{$table}'
EOD;
          $sql = debug_sql($sql);
          $res = $wpdb->get_results($sql);
     }
     return $res;
/*
     foreach($tables as $table){
          $conf = [
               'post_type'=>$table,
               'posts_per_page'=>-1
          ];
          $res = query_posts($conf);
          foreach($res as $post){
               $res = wp_delete_post($post->ID, true);
          }
     }
     return $res;
*/
}

function clean_client_threads(){

     $tables = [
          'surveyprint_book',
          'surveyprint_chapter',
          'surveyprint_panel',
          'surveyprint_section',
          'surveyprint_spread',
          'surveyprint_thread'
     ];

     global $wpdb;
     $res = null;
     foreach($tables as $table){

          $sql = <<<EOD
               delete from wp_posts where post_type = '{$table}'
EOD;
          $sql = debug_sql($sql);
          $res = $wpdb->get_results($sql);
     }
     return $res;
/*
     foreach($tables as $table){
          $conf = [
               'post_type'=>$table,
               'posts_per_page'=>-1
          ];
          $res = query_posts($conf);
          foreach($res as $post){
               $res = wp_delete_post($post->ID, true);
          }
     }
     return $res;
*/
}

function clean_bookbuilder_db(){

     $tables = [
          'surveyprint_asset',
          'surveyprint_book',
          'surveyprint_chapter',
          'surveyprint_layout',
          'surveyprint_panel',
          'surveyprint_question',
          'surveyprint_section',
          'surveyprint_spread',
          'surveyprint_survey',
          'surveyprint_thread',
          'surveyprint_toc'
     ];

     global $wpdb;
     $res = null;
     foreach($tables as $table){

          $sql = <<<EOD
               delete from wp_posts where post_type = '{$table}'
EOD;
          $sql = debug_sql($sql);
          $res = $wpdb->get_results($sql);
     }

     return $res;
/*
// cli interface using* mister pleaz
     foreach($tables as $table){
          $conf = [
               'post_type'=>$table,
               'posts_per_page'=>10
          ];
          $res = query_posts($conf);
          foreach($res as $post){
               $res = wp_delete_post($post->ID, true);
          }
     }
     return $res;
*/
}

function clean_survey_page(){

     $sql = <<<EOD
          delete from wp_posts where post_name like '%__survey__thread__view__%' and post_type = 'page'
EOD;
     global $wpdb;
     $sql = debug_sql($sql);
     $res = $wpdb->query($sql);
     return $res;

/*
     $conf = [
          'post_type'=>'page',
          'posts_per_page'=>-1,
          'meta_query'=>[
               'relation'=>'and', [
                    'key'=>'post_name',
                    'value'=>'%__survey__thread__view__%',
                    'compare'=>'like'
               ]
          ]
     ];
     $res = query_posts($conf);
     foreach($res as $post){
          $res = wp_delete_post($post->ID, true);
     }
     return $res;
*/
}

function init_survey_page(){

     clean_survey_page();

     $conti = <<<EOD
        <p>[survey_view]</p>
        <p>[constructor_view]</p>
EOD;

     $page_id = wp_insert_post([
          'post_author'=>get_author_id(),
          'post_content'=>$conti,
          'post_title'=>'Questionnaire',
          'post_status'=>'publish',
          'comment_status'=>'closed',
          'ping_status'=>'closed',
          'post_name'=>'__survey__thread__view__',
          'post_type'=>'page',
          'comment_count'=>0
     ]);

     return $page_id;
}

function dump_surveys(){

     $sql = <<<EOD
          select wp_posts.* from wp_posts where post_type = 'surveyprint_survey';
EOD;
     global $wpdb;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;

/*
     $conf = [
          'post_type'=>'surveyprint_survey',
          'orderby'=>'ID',
          'posts_per_page'=>-1
     ];
     $res = query_posts($conf);
     return $res;
*/
}

function dump_threads(){

     $sql = <<<EOD
          select wp_posts.* from wp_posts where post_type = 'surveyprint_thread';
EOD;
     global $wpdb;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);
     return $res;

/*
     $conf = [
          'post_type'=>'surveyprint_thread',
          'orderby'=>'ID',
          'posts_per_page'=>-1
     ];
     $res = query_posts($conf);
     return $res;
*/
}

function init_test_page(){

     $cont = <<<EOD
        <p>[survey_test_web_view]</p>
EOD;

     $page_id = wp_insert_post([
          'post_author'=>get_current_user_id(),
          'post_content'=>$cont,
          'post_title'=>'Questionnaire Test Walkthrough',
          'post_status'=>'publish',
          'comment_status'=>'closed',
          'ping_status'=>'closed',
          'post_name'=>'__survey_test_web_view__',
          'post_type'=>'page'
     ]);
}

