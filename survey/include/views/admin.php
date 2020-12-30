<?php defined('ABSPATH') || exit;

// plugin menu insert
add_action('admin_menu', 'setup_admin_menu');
function setup_admin_menu() {

     $page_title = 'surveyprint';
     $menu_title = esc_html(__('Book Builder', 'bookbuilder'));
     $menu_slug = 'surveyprint_admin_utils';
     $capability = 'administrator';
     $function = '';
     $icon_url = '';
     $position = '25';
     add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position); 

     $parent_slug = 'surveyprint_admin_utils';
     $menu_title = esc_html(__('Plugin Utilities', 'survey'));
     $page_title = 'utils';
     $menu_slug = 'surveyprint_utils';
     $capability = 'administrator';
     $function = 'build_surveyprint_utils_view';
     add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);

     $parent_slug = 'surveyprint_admin_utils';
     $menu_title = esc_html(__('Typeform Utilities', 'survey'));
     $page_title = 'utils';
     $menu_slug = 'typeform_utils';
     $capability = 'administrator';
     $function = 'build_typeform_utils_view';
     add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);

     $parent_slug = 'surveyprint_admin_utils';
     $menu_title = esc_html(__('Stored Questionnaire', 'survey'));
     $page_title = 'questionnaire';
     $menu_slug = 'questionnaire';
     $capability = 'administrator';
     $function = 'build_questionnaire_view';
     add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);

     $parent_slug = 'surveyprint_admin_utils';
     $menu_title = esc_html(__('Client Threads', 'survey'));
     $page_title = 'threads';
     $menu_slug = 'threads';
     $capability = 'administrator';
     $function = 'build_thread_view';
     add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);
/*
     $parent_slug = 'surveyprint_admin_utils';
     $menu_title = esc_html(__('Spread Manager', 'survey'));
     $page_title = 'spreads';
     $menu_slug = 'spreads';
     $capability = 'administrator';
     $function = 'build_spreads_view';
     add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);
*/
     $parent_slug = 'surveyprint_admin_utils';
     $page_title = 'layouts';
     $menu_slug = 'layouts';
     $capability = 'administrator';
     $menu_title = esc_html(__('Layout Manager', 'survey'));
     $function = 'build_layouts_view';
     add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);

     remove_submenu_page('surveyprint_admin_utils', 'surveyprint_admin_utils');
};

// surveyprint utilities
add_shortcode('surveyprint_utils_view', 'build_surveyprint_utils_view');
function build_surveyprint_utils_view(){

     wp_register_script('service_i18n', WP_PLUGIN_URL.SURVeY.'/js/services/i18n.js');
     wp_register_script(     'service', WP_PLUGIN_URL.SURVeY.'/js/services/admin.js', array('jquery'));

     wp_enqueue_script('service_i18n');
     wp_enqueue_script('service');

     $headline = esc_html(__('Book Builder Plugin Utilities', 'bookbuilder'));
     $welcome = esc_html(__('Welcome', 'bookbuilder'));
     echo <<<EOD
          <div class='wrap'>
               <h1 class='wp-heading-inline'>{$headline}</h1>
               <div class='page-title-action messages'><span>{$welcome}</span></div>
               <hr class='wp-header-end'>
     EOD;

     $actions = esc_html(__('Actions:', 'bookbuilder'));
     $init_survey_page = esc_html(__('Insert of the BookBuilder Webpage', 'bookbuilder'));
     $clean_survey_page = esc_html(__('Remove of the BookBuilder Webpage', 'bookbuilder'));
     $clean_surveys = esc_html(__('Remove of all stored Questionnaire', 'bookbuilder'));
     $clean_client_threads = esc_html(__('Remove of all Client Threads', 'bookbuilder'));
     $clean_bookbuilder_db = esc_html(__('Remove of all BookBuilder DB Entries', 'bookbuilder'));
     $clean_layouts = esc_html(__('Remove of all parsed Layout Records', 'bookbuilder'));
     $dump_surveys = esc_html(__('Dump of Stored Surveys', 'bookbuilder'));
     $dump_threads = esc_html(__('Dump of Client Threads', 'bookbuilder'));
     $edit = esc_html(__('Edit of a Questionnaire', 'bookbuilder'));
     echo <<<EOD
     <div class='edit'>
          <div class='unit'>{$actions}</div>
          <div><a href='javascript:initSurveyPage();'>{$init_survey_page}</a></div>
          <div><a href='javascript:cleanSurveyPage();'>{$clean_survey_page}</a></div>
          <div><a href='javascript:dumpSurveys();'>{$dump_surveys}</a></div>
          <div><a href='javascript:dumpClientThreads();'>{$dump_threads}</a></div>
          <div><a href='javascript:cleanSurveys();'>{$clean_surveys}</a></div>
          <div><a href='javascript:cleanClientThreads();'>{$clean_client_threads}</a></div>
          <div><a href='javascript:cleanLayouts();'>{$clean_layouts}</a></div>
          <div><a href='javascript:cleanSurveyDB();'>{$clean_bookbuilder_db}</a></div>
     </div>
EOD;

}

// typeform utilities
add_shortcode('typeform_utils_view', 'build_typeform_utils_view');
function build_typeform_utils_view(){

     wp_register_script(  'service_i18n', WP_PLUGIN_URL.SURVeY.'/js/services/i18n.js');
     wp_register_script(       'service', WP_PLUGIN_URL.SURVeY.'/js/services/admin.js', array('jquery'));

     wp_enqueue_script('service_i18n');
     wp_enqueue_script('service');

     $headline = esc_html(__('Book Builder Typeform Utilities', 'bookbuilder'));
     $welcome = esc_html(__('Welcome', 'bookbuilder'));
     echo <<<EOD
          <div class='wrap'>
               <h1 class='wp-heading-inline'>{$headline}</h1>
               <div class='page-title-action messages'><span>{$welcome}</span></div>
               <hr class='wp-header-end'>
     EOD;

     $auth_token = esc_html(__('Typeform Auth Token:', 'bookbuilder'));
     $download_survey = esc_html(__('Download of the Typeform Questionnaire', 'bookbuilder'));
     $actions = esc_html(__('Actions:', 'bookbuilder'));
     $bucket_name = esc_html(__('Bucket name:', 'bookbuilder'));
     $download_resultset = esc_html(__('Download of a Typeform Resultset', 'bookbuilder'));
     $construction = esc_html(__('Construciton of a Questionnaire', 'bookbuilder'));
     $edit = esc_html(__('Edit of a Questionnaire', 'bookbuilder'));
     $construct_fielding_questions = esc_html(__('Construction of the Fielding Questions from "./asset/typeform/201204-Cover-and-Pre--cMsCFF9a.json"', 'bookbuilder'));
     $construct_surveys_from_folder = esc_html(__('Construction of all Surveys from "./asset/typeform/*.json"', 'bookbuilder'));
     echo <<<EOD
     <div class='edit'>
          <div class='unit'>{$auth_token}</div>
          <div><input class='auth_token' type='text'></input></div>
          <div class='unit'>{$bucket_name}</div>
          <div><input class='bucket' type='text' value='N2BwhIXs'></input></div>
          <div><input class='filename' type='text' value='typeform_survey.json'></input></div>
          <div class='unit'>{$actions}</div>
          <div><a href='javascript:downloadTypeformSurvey();'>{$download_survey}</a></div>
          <div><a href='javascript:downloadTypeformSurveyResult();'>{$download_resultset}</a></div>
          <div><a href='javascript:constructTypeformSurvey();'>{$construction}</a></div>
          <div><a href='javascript:constructAllSurveys();'>{$construct_surveys_from_folder}</a></div>
          <div><a href='javascript:constructFieldingQuestions();'>{$construct_fielding_questions}</a></div>
     </div>
EOD;

}

add_shortcode('thread_view', 'build_thread_view');
function build_thread_view(){

     switch($_REQUEST['edit']){
          case 'entries':
               build_thread_entries_view();
               break; 
          default:
               build_thread_list_view();
               break;
     }
}

add_shortcode('thread_list_view', 'build_thread_list_view');
function build_thread_list_view(){

     $headline = esc_html(__('List of the Threads of some Client', 'bookbuilder'));
     $welcome = esc_html(__('Todo', 'bookbuilder'));

     wp_register_style('admin_style', WP_PLUGIN_URL.SURVeY.'/css/admin/style.css');
     wp_enqueue_style('admin_style');

     $headline = esc_html(__('Book Builder Client Threads', 'bookbuilder'));
     $id = esc_html(__('ID', 'bookbuilder'));
     $title = esc_html(__('Title', 'bookbuilder'));
     $excerpt = esc_html(__('Type', 'bookbuilder'));
     $date = esc_html(__('Date of Init', 'bookbuilder'));
     $author_id = esc_html(__('Author', 'bookbuilder'));
     $action = esc_html(__('Action', 'bookbuilder'));
     echo <<<EOD
          <div class='wrap'>
               <h1 class='wp-heading-inline'>{$headline}</h1>
               <div class='page-title-action messages'><span>{$welcome}</span></div>
               <hr class='wp-header-end'>

               <table class="wp-list-table widefat striped table-view-list posts">
               <thead>
                    <tr>
                    <th>{$id}</th>
                    <th>{$title}</th>
                    <th>{$excerpt}</th>
                    <th>{$date}</th>
                    <th>{$author_id}</th>
                    <th>{$action}</th>
               </tr>
               </thead>
EOD;

     $edit = esc_html(__('Entries', 'bookbuilder'));
     $style = 'column-primary';
     $coll = get_threads();
     if(!is_null($coll[0])){
          foreach($coll as $thread){
               $href = sprintf('%s?page=threads&edit=entries&thread_id=%s&client_id=%s', Path::SERVICE_BASE, $thread->ID, $thread->post_author);
               $d = date_create($thread->post_date);
               $d = date_format($d, 'd-m-Y H:i:s');
               echo '<tr>';
                    echo sprintf('<td class="%s">%s</td>', $style, esc_html($thread->ID));
                    echo sprintf('<td class="%s">%s</td>', $style, esc_html($thread->post_title));
                    echo sprintf('<td class="%s">%s</td>', $style, esc_html($thread->post_excerpt));
                    echo sprintf('<td class="%s">%s</td>', $style, esc_html($d));
                    echo sprintf('<td class="%s">%s</td>', $style, esc_html($thread->post_author));
                    echo sprintf('<td class="%s"><a href="%s">%s</a></td>', $style, $href, $edit);
               echo '</tr>';
          }
     };

     echo <<<EOD
               <tfoot>
                    <tr>
                         <th>{$id}</th>
                         <th>{$title}</th>
                         <th>{$excerpt}</th>
                         <th>{$date}</th>
                         <th>{$author_id}</th>
                         <th>{$action}</th>
                    </tr>
               </tfoot>
               </table>

      </div>
EOD;

}

add_shortcode('build_thread_entries_view', 'build_thread_entries_view');
function build_thread_entries_view(){

     wp_register_style('admin_style', WP_PLUGIN_URL.SURVeY.'/css/admin/style.css');
     wp_enqueue_style('admin_style');

     $thread_id = trim_incoming_numeric($_REQUEST['thread_id']);
     $client_id = trim_incoming_numeric($_REQUEST['client_id']);

     $thread = get_thread_by_id($thread_id, $client_id)[0];

     if(is_null($thread)){ return false; }
     $thread->post_content = pagpick($thread->post_content);

     // $toc = get_toc_by_thread_id($thread_id, $client_id)[0];
     // if(is_null($toc)){ return false; }
     // $toc->post_content = pagpick($toc->post_content);

     $sections = get_sections_by_thread_id($thread_id, $client_id);
     if(is_null($sections)){ return false; }

     $headline = esc_html(__('Book Builder Thread Fields from Book TOC', 'bookbuilder'));
     $id = esc_html(__('ID', 'bookbuilder'));
     $ref = esc_html(__('Reference', 'bookbuilder'));
     $title = esc_html(__('Title', 'bookbuilder'));
     $excerpt = esc_html(__('Type', 'bookbuilder'));
     $date = esc_html(__('Date of Init', 'bookbuilder'));
     $author_id = esc_html(__('Author', 'bookbuilder'));
     $action = esc_html(__('Action', 'bookbuilder'));
     $question = esc_html(__('Question', 'bookbuilder'));
     $answer = esc_html(__('Answer', 'bookbuilder'));
     $edit = esc_html(__('Edit', 'bookbuilder'));
     $assits = esc_html(__('Assets', 'bookbuilder'));
     $welcome = esc_html(__('Welcome', 'bookbuilder'));
     $section = esc_html(__('Section', 'bookbuilder'));

     echo <<<EOD
          <div class='wrap'>
               <h1 class='wp-heading-inline'>{$headline}</h1>
               <div class='page-title-action messages'><span>{$welcome}</span></div>
               <hr class='wp-header-end'>

               <table class="wp-list-table widefat striped table-view-list posts">
               <thead>
                    <tr>
                         <th>{$section}</th>
                         <th>{$id}</th>
                         <th>{$question}</th>
                         <th>{$answer}</th>
                         <th>{$assits}</th>
                         <th>{$date}</th>
                         <th>{$author_id}</th>
                         <th>{$action}</th>
                    </tr>
               </thead>
EOD;

     $style = 'column-primary';

     $section_id = $sections[0]->ID;

     foreach($thread->post_content['book'] as $item){

          $panel = get_panel_by_ref($item['sectionId'], $item['panelRef'], $client_id)[0];

          if(is_null($panel)){ continue; }
          $panel->post_content = pagpick($panel->post_content);

          $assets = get_assets_by_panel_ref($section_id, $panel->post_excerpt, 10, $client_id);

          $buf = '';
          foreach($assets as $asset){
               $buf.= sprintf('<img width="75px" src="%s">', add_base_to_chunk($asset->post_content));
          }

          $href = '#';
          $d = date_create($panel->post_date);
          $d = date_format($d, 'd-m-Y H:i:s');
          echo '<tr>';
               echo sprintf('<td class="%s">%s</td>', $style, esc_html($item['sectionId']));
               echo sprintf('<td class="%s">%s</td>', $style, esc_html($panel->ID));
               echo sprintf('<td class="%s">%s</td>', $style, esc_html($panel->post_content['question']));
               echo sprintf('<td class="%s">%s</td>', $style, esc_html($panel->post_content['answer']));
               echo sprintf('<td class="%s">%s</td>', $style, $buf);
               echo sprintf('<td class="%s">%s</td>', $style, esc_html($d));
               echo sprintf('<td class="%s">%s</td>', $style, esc_html($panel->post_author));
               echo sprintf('<td class="%s"><a href="%s">%s</a></td>', $style, $href, $edit);
          echo '</tr>';
     }
echo <<<EOD
               <tfoot>
                    <tr>
                         <th>{$section}</th>
                         <th>{$id}</th>
                         <th>{$question}</th>
                         <th>{$answer}</th>
                         <th>{$assits}</th>
                         <th>{$date}</th>
                         <th>{$author_id}</th>
                         <th>{$action}</th>
                    </tr>
               </tfoot>
          </table>
EOD;

}

add_shortcode('questionnaire_view', 'build_questionnaire_view');
function build_questionnaire_view(){

     switch($_REQUEST['edit']){

          case 'questions':
               build_question_view();
               break;
 
          case 'survey_printrules':
               if(false == empty($_POST)){ 
                    print_r(sprintf("<textarea>%s</textarea>", json_encode($_POST, JSON_PRETTY_PRINT)));
               };
               build_question_view();
               break;

          default:
               build_questionnaire_list_view();
               break;
     }
}

add_shortcode('question_view', 'build_question_view');
function build_question_view(){

     $survey_id = $_REQUEST['survey_id'];
     $coll = get_questions_by_survey_id($survey_id);

     wp_register_style('admin_style', WP_PLUGIN_URL.SURVeY.'/css/admin/style.css');
     wp_enqueue_style('admin_style');
 
     wp_register_script('service', WP_PLUGIN_URL.SURVeY.'/js/services/admin.js', array('jquery'));
     wp_register_script('service_i18n', WP_PLUGIN_URL.SURVeY.'/js/services/i18n.js');

     wp_enqueue_script('service');
     wp_enqueue_script('service_i18n');

     $message = esc_html(__('List of Questions', 'bookbuilder'));
     $id = esc_html(__('ID', 'bookbuilder'));
     $title = esc_html(__('Question', 'bookbuilder'));
     $headline = esc_html(__('Book Builder Stored Questionnaire', 'bookbuilder'));
     $welcome = esc_html(__('Welcome', 'bookbuilder'));
     $excerpt = esc_html(__('Reference', 'bookbuilder'));
     $parent = esc_html(__('Group', 'bookbuilder'));
     $date = esc_html(__('Date of Init', 'bookbuilder'));
     $rulez = esc_html(__('Spread Rulez', 'bookbuilder'));
     $save_input = esc_html(__('Save', 'bookbuilder'));
     $apply = esc_html(__('Apply', 'bookbuilder'));

     $href = sprintf('%s?page=questionnaire&edit=survey_printrules&survey_id=%s', Path::SERVICE_BASE, $survey_id);
     echo <<<EOD

          <div class='wrap'>
               <h1 class='wp-heading-inline'>{$headline}</h1>
               <div class='page-title-action messages'><span>{$welcome}</span></div>
               <hr class='wp-header-end'>

          <form method='post' action='{$href}'>

          <table class="wp-list-table widefat striped table-view-list posts">

          <thead>
               <tr>
                    <th>{$id}</th>
                    <th>{$excerpt}</th>
                    <th>{$date}</th>
                    <th>{$parent}</th>
                    <th>{$title}</th>
                    <th>{$rulez}</th>
               </tr>
          </thead>
EOD;

     $asset_options = <<<EOD
          <option value='0'>0</option>
          <option value='1'>1</option>
          <option value='2'>2</option>
          <option value='3'>3</option>
          <option value='4'>4</option>
          <option value='5'>5</option>
          <option value='6'>6</option>
          <option value='7'>7</option>
          <option value='8'>8</option>
          <option value='9'>9</option>
EOD;

     $layout_options = <<<EOD
          <option value='default'>Default Layout Group 0th</option>
          <option value='default1st'>Default Layout Group 1st</option>
          <option value='default2nd'>Default Layout Group 2nd</option>
          <option value='default3rd'>Default Layout Group 3rd</option>
          <option value='default3rd'>Default Layout Group 5th</option>
EOD;


     foreach($coll as $question){
          $question->post_content = pagpick($question->post_content);
          $question_id = $question->ID;

          $node_style = '';
          $rule_input = '';
          if('group' == $question->post_content['type']){
               $node_style = 'group-title';
          }

          $rule_input = <<<EOD
     <textarea name='layout_rule_of_question_{$question_id}_is'></textarea>
EOD;

          $parent = '';
          if(!is_null($question->post_content['conf']['parent'])){
               $parent = $question->post_content['conf']['parent'];
          }

          $d = date_create($question->post_date);
          $d = date_format($d, 'd.m.Y H:i:s');

          echo '<tr>';
          echo sprintf('<td>%s</td>', esc_html($question->ID));
          echo sprintf('<td>%s</td>', esc_html($question->post_excerpt));
          echo sprintf('<td>%s</td>', esc_html($d));
          echo sprintf('<td>%s</td>', esc_html($parent));
          echo sprintf('<td class="%s">%s</td>', $node_style, esc_html($question->post_content['title']));
          echo sprintf('<td>%s</td>', $rule_input);
          echo '</tr>';
     }

     echo <<<EOD
          <tfoot>
               <tr>
                    <th>{$id}</th>
                    <th>{$excerpt}</th>
                    <th>{$date}</th>
                    <th>{$parent}</th>
                    <th>{$title}</th>
                    <th>{$rulez}</th>
               </tr>
          </tfoot>
          </table>

          <input type='submit' id='doaction' class='button action' value='{$apply}'>

          </form>
EOD;

}

add_shortcode('questionnaire_list_view', 'build_questionnaire_list_view');
function build_questionnaire_list_view(){

     $coll = get_surveys();
// print 'build_questionnaire_list_view(): get_surveys(): ';
// print_r($coll);
// print "\n";

     $message = esc_html(__('List of stored Questionnaire:', 'bookbuilder'));
     $id = esc_html(__('ID', 'bookbuilder'));
     $excerpt = esc_html(__('Reference', 'bookbuilder'));
     $title = esc_html(__('Title', 'bookbuilder'));
     $date = esc_html(__('Date of Init', 'bookbuilder'));
     $action = esc_html(__('Action', 'bookbuilder'));
     $edit = esc_html(__('Edit Fields', 'bookbuilder'));
     $headline = esc_html(__('Questionnaire', 'bookbuilder'));
     $welcome = esc_html(__('Welcome', 'bookbuilder'));

     echo <<<EOD
          <div class='wrap'>
               <h1 class='wp-heading-inline'>{$headline}</h1>
               <div class='page-title-action messages'><span>{$welcome}</span></div>
               <hr class='wp-header-end'>

          <table class="wp-list-table widefat striped table-view-list posts">
          <thead>
               <tr>
                    <th>{$id}</th>
                    <th>{$title}</th>
                    <th>{$excerpt}</th>
                    <th>{$date}</th>
                    <th>{$action}</th>
               </tr>
          </thead>
EOD;

     $style = 'column-primary';
     if(!is_null($coll[0])){
          foreach($coll as $survey){
               $href = sprintf('%s?page=questionnaire&edit=questions&survey_id=%s', Path::SERVICE_BASE, $survey->ID);
               $d = date_create($survey->post_date);
               $d = date_format($d, 'd-m-Y H:i:s');
               echo '<tr>';
                    echo sprintf('<td class="%s">%s</td>', $style, esc_html($survey->ID));
                    echo sprintf('<td class="%s">%s</td>', $style, esc_html($survey->post_title));
                    echo sprintf('<td class="%s">%s</td>', $style, esc_html($survey->post_name));
                    echo sprintf('<td class="%s">%s</td>', $style, esc_html($d));
                    echo sprintf('<td class="%s"><a href="%s">%s</a></td>', $style, $href, $edit);
               echo '</tr>';
          }
     };

     echo <<<EOD
          <tfoot>
               <tr>
                    <th>{$id}</th>
                    <th>{$title}</th>
                    <th>{$excerpt}</th>
                    <th>{$date}</th>
                    <th>{$action}</th>
               </tr>
          </tfoot>
EOD;

     echo '</table>';
}

add_shortcode('spreads_view', 'build_spreads_view');
function build_spreads_view() {

     wp_register_script(     'viewer-config', WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/src/main/config-spreads.js');
     wp_register_script(       'viewer-main', WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/src/main/main.js');
     wp_register_script(      'viewer-tools', WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/src/main/module/tools/main.js');
     wp_register_script(     'viewer-screen', WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/src/main/module/screen/main.js');
     wp_register_script(    'viewer-correct', WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/src/main/module/screen/correct.js');
     wp_register_script(     'viewer-bitmap', WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/src/main/module/screen/bitmap.js');
     wp_register_script(        'viewer-svg', WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/lib/svg.js');
     wp_register_script('viewer-layout_util', WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/src/main/module/util/main.js');
     wp_register_script( 'viewer-layout_net', WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/src/main/module/net/main.js');
     wp_register_script('viewer-layout_init', WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/init.js', array('jquery'));

     wp_enqueue_script('viewer-config');
     wp_enqueue_script('viewer-main');
     wp_enqueue_script('viewer-tools');
     wp_enqueue_script('viewer-bitmap');
     wp_enqueue_script('viewer-correct');
     wp_enqueue_script('viewer-screen');
     wp_enqueue_script('viewer-svg');
     wp_enqueue_script('viewer-layout_util');
     wp_enqueue_script('viewer-layout_net');
     wp_enqueue_script('viewer-layout_init');

     wp_register_style('constructor_style', WP_PLUGIN_URL.SURVeY.'/css/spread-viewer/style.css');
     wp_enqueue_style('constructor_style');

     $headline = esc_html(__('Spread Manager', 'bookbuilder'));
     $welcome = esc_html(__('Welcome', 'bookbuilder'));
     $res = <<<EOD
     <div class='wrap'>
          <h1 class='wp-heading-inline'>{$headline}</h1>
          <div class='page-title-action'><span>{$welcome}</span></div>
          <hr class='wp-header-end'>
          <div class='layout-edit'>
               <div class='layout-messages'></div>
               <div class='layout-main'>
                    <div class='layout-controls'>
                         <div class='layout-pages'></div>
                         <div class='layout-tools'></div>
                         <div class='layout-library'></div>
                         <div class='layout-actions'></div>
                    </div>
                    <div class='layout-rows'>
                         <div class='layout-buff'><div class='screen'></div></div>
                    </div>
               </div>
          </div>
          <div class='offscreen'></div>
          <div class='printscreen'></div>
     </div>
EOD;

     echo $res;
}

add_shortcode('layouts_view', 'build_layouts_view');
function build_layouts_view() {

     wp_register_script(     'viewer-config', WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/src/main/config-layouts.js');
     wp_register_script(       'viewer-main', WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/src/main/main.js');
     wp_register_script(      'viewer-tools', WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/src/main/module/tools/main.js');
     wp_register_script(     'viewer-screen', WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/src/main/module/screen/main.js');
     wp_register_script(    'viewer-correct', WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/src/main/module/screen/correct.js');
     wp_register_script(     'viewer-bitmap', WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/src/main/module/screen/bitmap.js');
     wp_register_script(        'viewer-svg', WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/lib/svg.js');
     wp_register_script('viewer-layout_util', WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/src/main/module/util/main.js');
     wp_register_script( 'viewer-layout_net', WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/src/main/module/net/main.js');
     wp_register_script('viewer-layout_init', WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/init.js', array('jquery'));

     wp_enqueue_script('viewer-config');
     wp_enqueue_script('viewer-main');
     wp_enqueue_script('viewer-tools');
     wp_enqueue_script('viewer-bitmap');
     wp_enqueue_script('viewer-correct');
     wp_enqueue_script('viewer-screen');
     wp_enqueue_script('viewer-svg');
     wp_enqueue_script('viewer-layout_util');
     wp_enqueue_script('viewer-layout_net');
     wp_enqueue_script('viewer-layout_init');

     wp_register_style('viewer_style', WP_PLUGIN_URL.SURVeY.'/css/spread-viewer/style.css');
     wp_enqueue_style('viewer_style');

     $headline = esc_html(__('Layout Manager', 'bookbuilder'));
     $welcome = esc_html(__('Welcome', 'bookbuilder'));
     $res = <<<EOD
     <div class='wrap'>
          <h1 class='wp-heading-inline'>{$headline}</h1>
          <div class='page-title-action'><span>{$welcome}</span></div>
          <hr class='wp-header-end'>
          <div class='layout-edit'>
               <div class='layout-messages'></div>
               <div class='layout-main'>
                    <div class='layout-controls'>
                         <div class='layout-pages'></div>
                         <div class='layout-toolbar'></div>
                         <div class='layout-controlbar'></div>
                         <div class='layout-tools'></div>
                         <div class='layout-library'></div>
                         <div class='layout-actions'></div>
                    </div>
                    <div class='layout-rows'>
                         <div class='layout-buff'><div class='screen'></div></div>
                    </div>
               </div>
          </div>
          <div class='offscreen'></div>
          <div class='printscreen'></div>
     </div>
EOD;
     echo $res;
}
