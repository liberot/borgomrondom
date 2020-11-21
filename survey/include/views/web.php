<?php defined('ABSPATH') || exit();

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

     wp_register_script('navigation_utility', WP_PLUGIN_URL.SURVeY.'/js/web-client/navigation.js', array('jquery'));
     wp_enqueue_script('navigation_utility');

     wp_register_style('web_style', WP_PLUGIN_URL.'/nosuch/survey/css/web-client/web.css');
     wp_enqueue_style('web_style');

     if(is_null($_REQUEST['thread_id'])){
          build_start_thread_view();
          return;
     }

     if('init' == $_REQUEST['thread_id']){
          init_survey_thread();
          build_panel_view();
          return;
     }

     eval_next_panel();
     build_panel_view();
}



function build_start_thread_view(){

// sets up lets get it on click
     $page_id = $_REQUEST['page_id'];
     $href = sprintf('?page_id=%s&thread_id=%s', $page_id, 'init');
     echo sprintf('<span><a href="%s">%s</a></span>', $href, esc_html(__('Lets get it on', 'nosuch')));
}



function init_survey_thread(){

// sets up a new therad
     $coll = init_guest_thread();

// reads resources
     $toc = $coll['toc'][0];
     $toc->post_content = pagpick($toc->post_content); 
     $panel_ref = $toc->post_content['init_refs'][0];

     $thread = $coll['thread'][0];
     $section = $coll['sections'][0];
     $author_id = get_author_id();

// saves toc
     // $toc = get_toc_by_thread_id($thread_id)[0];
     $toc->post_content['thread_log'] = []; 
     $toc->post_content['history'] = [];
     $toc->post_content['histroy'][] = $panel_ref; 
     $toc->post_content['navstep'] = 0; 
     $toc->post_content['tocstep'] = 0; 
     $toc->post_content = pigpack($toc->post_content);
     $conf = [
          'post_type'=>'surveyprint_toc',
          'post_author'=>$author_id,
          'post_title'=>$toc->post_title,
          'post_name'=>$surveyprint_uuid,
          'post_excerpt'=>$toc->post_excerpt,
          'post_parent'=>$thread_id,
          'post_content'=>$toc->post_content
     ];
     $toc_id = save_toc($conf);

// sets session tickets
     set_session_ticket('thread_id', $thread->ID, true);
     set_session_ticket('section_id', $section->ID, true);
     set_session_ticket('panel_ref', $panel_ref, true);
}



function build_panel_view(){

     $section_id = get_session_ticket('section_id');
     $panel_ref = get_session_ticket('panel_ref');
     $panel = get_panel_by_ref($section_id, $panel_ref)[0];
     if(null == $panel){ return false; }
     $panel->post_content = pagpick($panel->post_content);

     switch($panel->post_content['type']){
          case 'short_text':
              build_short_text_panel($panel);
              break;
     }

}



function eval_next_panel(){

// reads resources
     $thread_id = $_REQUEST['thread_id'];
     $section_id = $_REQUEST['section_id'];
     $panel_ref = $_REQUEST['panel_ref'];

print $panel_ref.'<br/>';

     $thread_id = get_session_ticket('thread_id');
     $section_id = get_session_ticket('section_id');
     $panel_ref = get_session_ticket('panel_ref');
     $next_panel_ref = get_session_ticket('next_panel_ref');

print $panel_ref.'<br/>';

/*
print '>>';
print $thread_id;
print PHP_EOL;
print $section_id;
print PHP_EOL;
print $panel_ref;
print PHP_EOL;
*/

// sets up panel
     $panel = get_panel_by_ref($section_id, $panel_ref)[0];
     $panel->post_content = pagpick($panel->post_content);
     $toc = get_toc_by_thread_id($thread_id)[0];
     $toc->post_content = pagpick($toc->post_content); 

// evaluates next panel by rulez 
     $rulez = $toc->post_content['rulez'];
     $link = null;
     foreach($rulez as $rule){
          if($panel_ref != $rule['ref']){
               // continue;
          }
          foreach($rule['actions'] as $action){
               $condition = eval_condition($action['condition'], $toc->post_content['thread_log']);
               if(false == $condition){
                    continue;
               }
          }
// reads link from toc
          if(null == $link){
               $toc->post_content['navstep'] = intval($toc->post_content['navstep']) +1;
               if($stoc->post_content['navstep'] >= count($toc->post_content['init_refs']) -1){
                    $toc->post_content['navstep'] = count($toc->post_content['init_refs']) -1;
               }
               $link = $toc->post_content['init_refs'][$toc->post_content['navstep']];
          }
     }

// sets session
     $next_panel_ref = $link;
     set_session_ticket('panel_ref', $panel_ref, true);

// saves panel
     if(is_null($_REQUEST['panel_ref'])){
          return;
     }

     if(is_null($_REQUEST['input_ref'])){
          return;
     }

     $panel->post_content['question'] = $panel->post_content['title'];
     $panel->post_content['answer'] = trim_incoming_answer($_POST['answer']);
     $panel = pigpack($panel);
     $conf = [
          'post_author'=>$author_id,
          'post_type'=>'surveyprint_panel',
          'post_parent'=>$thread_id,
          'post_excerpt'=>$panel_ref,
          'post_content'=>$panel
     ];
     $panel_id = init_panel($conf);

// saves toc
     $ref = $panel_ref;
     $val = $panel->post_content['answer'];
     $toc->post_content['history'][] = $panel_ref; 
     $toc->post_content['thread_log'][$ref] = $val; 
     $toc->post_content = pigpack($toc->post_content);
     $conf = [
          'post_type'=>'surveyprint_toc',
          'post_author'=>$author_id,
          'post_title'=>$toc->post_title,
          'post_name'=>$surveyprint_uuid,
          'post_excerpt'=>$toc->post_excerpt,
          'post_parent'=>$thread_id,
          'post_content'=>$toc->post_content
     ];
     $toc_id = save_toc($conf);

// steps
     set_session_ticket('panel_ref', $next_panel_ref, true);
}



function eval_condition($condition, $log){
     $condition = eval_leaf($condition, $log);
     $condition = eval_node($condition, $log);
}

function eval_node($node, $log){
     foreach($node as $key=>$val){
          if(is_array($val)){
               $node[$key] = eval_leaf($val, $log);
               continue;
          }
          switch($key){
               case 'op':
                    switch($val){
                         case 'and':
                              $node['result'] = 'false';
                              break;
                         case 'or':
                              $node['result'] = 'false';
                              break;
                    }
          }
     }
     return $node;
}

function eval_leaf($node, $log){
     foreach($node as $key=>$val){
          if(is_array($val)){
               $node[$key] = eval_leaf($val, $log);
               continue;
          }
          switch($key){
               case 'op':
                    switch($val){
                         case 'is':
                              $temp = [];
                              foreach($node['vars'] as $item){ $temp[$item['type']] = $item['value']; }
                              $node['result'] = 'false';
                              break;
                         case 'answered':
                              $temp = [];
                              foreach($node['vars'] as $item){ $temp[$item['type']] = $item['value']; }
                              $node['vars'] = $temp;
                              $node['result'] = 'false';
                              break;
                    }
                    break;
          }
          /*
          print "\n";
          switch($key){
               case 'op':
                    print 'op: ';
                    print_r($val);
                    print "\n";
                    break;
               default:
                    print 'vr: ';
                    print_r($val);
                    print "\n";
                    break;
           }
           */
     }
     return $node;
}






function build_short_text_panel($panel){

     $page_id = $_REQUEST['page_id'];
     $section_id = get_session_ticket('section_id');
     $thread_id = get_session_ticket('thread_id');

     $panel_prev = get_session_ticket('panel_ref');
     $panel_ref = $panel->post_content['ref'];

     $title = $panel->post_content['title'];

     $send = __('Send', 'nosuch');
     $action = sprintf('?page_id=%s&thread_id=%s&section_id=%s&panel_ref=%s', $page_id, $thread_id, $section_id, $panel_ref);

     echo <<<EOD

<form method='POST' action='{$action}'>
<p class='short-text'>{$title}</p>
<input type='hidden' name='panel_ref' value='{$panel_ref}'></input>
<input type='hidden' name='input_ref' value='{$panel_ref}'></input>
<p class='short-text'><textarea name='answer'></textarea></p>
<p class='short-text'><input type='submit' value='{$send}'></input></p>
</form>

EOD;

     return true;
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


