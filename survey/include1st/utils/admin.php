<?php defined('ABSPATH') || exit;

function delete_layouts(){

     global $wpdb;
     $prefix = $wpdb->prefix;

     $tables = [
          'surveyprint_layout',
     ];

     $res = null;
     foreach($tables as $table){
          $sql = <<<EOD
               delete from {$prefix}posts where post_type = '{$table}'
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

function delete_surveys(){

     $tables = [
          'surveyprint_question',
          'surveyprint_section',
          'surveyprint_thread'
     ];

     global $wpdb;
     $prefix = $wpdb->prefix;
     $res = null;
     foreach($tables as $table){

          $sql = <<<EOD
               delete from {$prefix}posts where post_type = '{$table}'
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

function delete_client_threads(){

     $tables = [
          'surveyprint_book',
          'surveyprint_chapter',
          'surveyprint_panel',
          'surveyprint_section',
          'surveyprint_spread',
          'surveyprint_thread'
     ];

     global $wpdb;
     $prefix = $wpdb->prefix;
     $res = null;
     foreach($tables as $table){

          $sql = <<<EOD
               delete from {$prefix}posts where post_type = '{$table}'
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

function delete_bookbuilder_db(){

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
     $prefix = $wpdb->prefix;
     $res = null;
     foreach($tables as $table){

          $sql = <<<EOD
               delete from {$prefix}posts where post_type = '{$table}'
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

function delete_survey_page(){

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
          delete from {$prefix}posts 
               where post_type = 'page' 
               and post_title = 'Questionnaire' 
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->query($sql);

     return $res;

/*
     $conf = [
          'post_type'=>'page',
          'posts_per_page'=>-1,
          'meta_query'=>[
               'relation'=>'and', [
                    'key'=>'post_title',
                    'value'=>'%Questionnaire%',
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

function delete_survey_by_id($survey_id){
     $survey_id = esc_sql($survey_id);
     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          delete from {$prefix}posts
               where post_type = 'surveyprint_survey'
               and ID = '{$survey_id}'
EOD;
     $sql = debug_sql($sql);
     $res = $wpdb->query($sql);
     return $res;
}

function init_survey_page(){

     delete_survey_page();

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
          'post_name'=>'bookbuilder',
          'post_type'=>'page',
          'comment_count'=>0
     ]);

     return $page_id;
}

function dump_surveys(){

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select {$prefix}posts.* from {$prefix}posts where post_type = 'surveyprint_survey';
EOD;
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

     global $wpdb;
     $prefix = $wpdb->prefix;
     $sql = <<<EOD
          select {$prefix}posts.* from {$prefix}posts where post_type = 'surveyprint_thread';
EOD;
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

// fixdiss x2
function init_spread_state($question_id, $show_spread_state){

     $question_id = esc_sql($question_id);
     $show_spread_state = esc_sql($show_spread_state);
     $author_id = esc_sql(get_author_id());

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
          select 
               {$prefix}posts.*  
               from {$prefix}posts 
               where post_type = 'surveyprint_question'
               and ID = '{$question_id}'
               order by ID
               limit 1
EOD;
     $sql = debug_sql($sql);
     $question = $wpdb->get_results($sql)[0];
     if(is_null($question)){
          return false;
     }

     $question->post_author = $author_id;

     $question->post_content = pagpick($question->post_content);
     $question->post_content['show_spread_state'] = $show_spread_state;
     $question->post_content = pigpack($question->post_content);

     $res = wp_insert_post($question);

     return $res;
}

function init_redirect($question_id, $survey_id){

     $question_id = esc_sql($question_id);
     $survey_id = esc_sql($survey_id);
     $author_id = esc_sql(get_author_id());

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
          select 
               {$prefix}posts.*  
               from {$prefix}posts 
               where post_type = 'surveyprint_question'
               and ID = '{$question_id}'
               order by ID
               limit 1
EOD;
     $sql = debug_sql($sql);
     $question = $wpdb->get_results($sql)[0];
     if(is_null($question)){
          return false;
     }

     $question->post_author = $author_id;

     $question->post_content = pagpick($question->post_content);
     $question->post_content['redirect_survey_id'] = $survey_id;
     $question->post_content = pigpack($question->post_content);

     $res = wp_insert_post($question);

     return $res;
}

function get_author_by_id($author_id){

     $author_id = esc_sql($author_id);

     global $wpdb;
     $prefix = $wpdb->prefix;

     $sql = <<<EOD
          select * 
               from {$prefix}users
               where ID = '{$author_id}'
EOD;

     $sql = debug_sql($sql);

     $res = $wpdb->get_results($sql);

     return $res;
}
