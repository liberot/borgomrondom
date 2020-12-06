<?php defined('ABSPATH') || exit;

function clean_surveys(){
     global $wpdb;

     $tables = [
          'surveyprint_question',
          'surveyprint_section',
          'surveyprint_thread'
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
}

function clean_client_threads(){
     global $wpdb;

     $tables = [
          'surveyprint_book',
          'surveyprint_chapter',
          'surveyprint_panel',
          'surveyprint_section',
          'surveyprint_spread',
          'surveyprint_thread'
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
}

function clean_bookbuilder_db(){
     global $wpdb;

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

     $res = null;

     foreach($tables as $table){

          $sql = <<<EOD
               delete from wp_posts where post_type = '{$table}'
EOD;
          $sql = debug_sql($sql);
          $res = $wpdb->get_results($sql);
     }

     return $res;
}

function init_survey_page(){
     $sql = <<<EOD
          delete from wp_posts where post_name = '__survey__thread__view__' and post_type = 'page'
EOD;
     global $wpdb;
     $sql = debug_sql($sql);
     $res = $wpdb->query($sql);

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
}

