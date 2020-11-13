<?php defined('ABSPATH') || exit;

function init_test_page(){
     $sql = <<<EOD
          select * from wp_posts where post_type = 'page' and post_name = '__survey_test_web_view__';
EOD;
     global $wpdb;
     $sql = debug_sql($sql);
     $res = $wpdb->get_results($sql);

     if(null != $res[0]){ return false; }

     $conti = <<<EOD
        <p>[survey_test_web_view]</p>
EOD;

     $page_id = wp_insert_post([
          'post_author'=>get_current_user_id(),
          'post_content'=>$conti,
          'post_title'=>'Questionnaire Test Walkthrough',
          'post_status'=>'publish',
          'comment_status'=>'closed',
          'ping_status'=>'closed',
          'post_name'=>'__survey_test_web_view__',
          'post_type'=>'page'
     ]);
}

add_shortcode('survey_test_web_view', 'build_survey_test_web_view');
function build_survey_test_web_view() {
     if(true != is_user_logged_in()){
          $message = esc_html(__('', 'nosuch'));
          return '<p><a href="/wp-login.php">'.$message.'</p>';
     }
     wp_register_style('web_style', WP_PLUGIN_URL.'/nosuch/survey/css/web-client/web.css');
     wp_enqueue_style('web_style');

     if(!is_null($_REQUEST['page_id'])){
          build_survey_list_web_view();
     }
     if(!is_null($_REQUEST['survey_id'])){
          build_survey_web_view($_REQUEST['survey_id']);
     }
     if(!is_null($_REQUEST['thread_id'])){
          build_thread_web_view($_REQUEST['thread_id']);
     }
}

function build_survey_list_web_view(){
     $coll = get_surveys();
     $page_id = $_REQUEST['page_id'];
     echo '<p>';
     foreach($coll as $survey){
          $href = sprintf('?page_id=%s&survey_id=%s', $page_id, $survey->ID);
          echo sprintf('<span class="survey-list-entry"><a href="%s">%s</a></span>', $href, $survey->post_title);
     }
     echo '</p>';
}

function build_survey_web_view($survey_id){
     $page_id = $_REQUEST['page_id'];
     $survey = get_survey_by_id($survey_id)[0];
     if(null == $survey){ return false; }
     echo sprintf('<p><span class="survey-title">%s</a></p>', $survey->post_title);
     $threads = get_threads_by_survey_id($survey_id);
     if(null == $threads){ return false; }
     foreach($threads as $thread){
          $href = sprintf('?page_id=%s&survey_id=%s&thread_id=%s', $page_id, $survey_id, $thread->ID);
          echo sprintf('<span class="survey-list-entry"><a href="%s">%s</a></span>', $href, $thread->post_date);
     }
}

function build_thread_web_view($thread_id){
     $page_id = $_REQUEST['page_id'];
     $survey_id = $_REQUEST['survey_id'];
     $thread = get_thread_by_id($thread_id)[0];
     $toc = get_toc_by_thread_id($thread_id)[0];
     if(null == $thread){ return false; }
     if(null == $toc){ return false; }
     $toc->post_content = pagpick($toc->post_content);
     $init_refs = $toc->post_content['init_refs'];
     if(is_null($init_refs)){ return false; }
     $pos = 0;
     $question_ref = $_REQUEST['question_ref'];
     if(is_null($question_ref)){ $question_ref = $init_refs[$pos]; }
     else {
          $pos = array_search($question_ref, $init_refs);
          $pos+= 1;
          $question_ref = $init_refs[$pos];
          if(is_null($question_ref)){ 
               $question_ref = $init_refs[0]; 
          }
     }
     $panel = get_panel_by_ref($thread_id, $init_refs[$pos])[0];
     if(is_null($panel)){ return false; }
     $panel->post_content = pagpick($panel->post_content);
     echo sprintf('<p><span class="question-out">%s</span></p>', $panel->post_content['question']);
     $next_question = esc_html(__('Next Question', 'nosuch'));
     $href = sprintf('?page_id=%s&survey_id=%s&thread_id=%s&question_ref=%s', $page_id, $survey_id, $thread_id, $question_ref);
     switch($panel->post_content['type']){
          case 'short_text':
               echo sprintf('<p><textarea class="answer-in"></textarea></p>');
               echo sprintf('<p><a href="%s">%s</p>', $href, $next_question);
               break;
     }
}





/*
add_filter('the_content', 'filter_the_content');
function filter_the_content($content) { 
}
add_filter('the_post', 'filter_the_post');
function filter_the_post($content) {
}
*/


