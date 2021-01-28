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
     $delete_survey_page = esc_html(__('Remove of the BookBuilder Webpage', 'bookbuilder'));
     $delete_surveys = esc_html(__('Remove of all stored Questionnaire', 'bookbuilder'));
     $delete_client_threads = esc_html(__('Remove of all Client Threads', 'bookbuilder'));
     $delete_bookbuilder_db = esc_html(__('Remove of all BookBuilder DB Entries', 'bookbuilder'));
     $delete_layouts = esc_html(__('Remove of all parsed Layout Records', 'bookbuilder'));
     $dump_surveys = esc_html(__('Dump of Stored Surveys', 'bookbuilder'));
     $dump_threads = esc_html(__('Dump of Client Threads', 'bookbuilder'));
     $edit = esc_html(__('Edit of a Questionnaire', 'bookbuilder'));
     echo <<<EOD
     <div class='edit'>
          <div class='unit'>{$actions}</div>
          <div><a href='javascript:initSurveyPage();'>{$init_survey_page}</a></div>
          <div><a href='javascript:deleteSurveyPage();'>{$delete_survey_page}</a></div>
          <div><a href='javascript:dumpSurveys();'>{$dump_surveys}</a></div>
          <div><a href='javascript:dumpClientThreads();'>{$dump_threads}</a></div>
          <div><a href='javascript:deleteSurveys();'>{$delete_surveys}</a></div>
          <div><a href='javascript:deleteClientThreads();'>{$delete_client_threads}</a></div>
          <div><a href='javascript:deleteLayouts();'>{$delete_layouts}</a></div>
          <div><a href='javascript:deleteSurveyDB();'>{$delete_bookbuilder_db}</a></div>
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
/*
     $construct_fielding_questions = esc_html(
          __('Construction of the Fielding Questions from "./asset/typeform/201204-Cover-and-Pre--cMsCFF9a.json"', 'bookbuilder')
     );
*/
     $construct_fielding_questions = esc_html(
          __('Construction of the Fielding Questions from "./asset/typeform/BBC0-Cover-and-Prefa--FvSIczF7.json"', 'bookbuilder')
     );
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

     switch($_REQUEST['action']){

          case 'edit':
               build_thread_entries_view();
               break; 

          case 'delete':
               $thread_id = $_REQUEST['thread_id'];
               $client_id = $_REQUEST['client_id'];
               if(!is_null($thread_id) && !is_null($thread_id)){
                    delete_thread_by_id($thread_id, $client_id);
               }
               build_thread_list_view();
               break;

          default:
               build_thread_list_view();
               break;
     }
}

add_shortcode('thread_list_view', 'build_thread_list_view');
function build_thread_list_view(){

     $headline = esc_html(__('List of the Threads', 'bookbuilder'));
     $welcome = esc_html(__('Todo', 'bookbuilder'));

     wp_register_style('admin_style', WP_PLUGIN_URL.SURVeY.'/css/admin/style.css');
     wp_enqueue_style('admin_style');

     $headline = esc_html(__('Book Builder Client Threads', 'bookbuilder'));
     $id = esc_html(__('ID', 'bookbuilder'));
     $title = esc_html(__('Title', 'bookbuilder'));
     $excerpt = esc_html(__('Type', 'bookbuilder'));
     $date = esc_html(__('Date of Init', 'bookbuilder'));
     $author_id = esc_html(__('Author', 'bookbuilder'));
     $edit = esc_html(__('Edit', 'bookbuilder'));
     $delete = esc_html(__('Delete', 'bookbuilder'));
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
                    <th>{$edit}</th>
                    <th>{$delete}</th>
               </tr>
               </thead>
EOD;

     $style = 'column-primary';
     $coll = get_threads();
     if(!is_null($coll[0])){
          foreach($coll as $thread){
               $href1st = sprintf('%s?page=threads&action=edit&thread_id=%s&client_id=%s', Path::SERVICE_BASE, $thread->ID, $thread->post_author);
               $href2nd = sprintf('%s?page=threads&action=delete&thread_id=%s&client_id=%s', Path::SERVICE_BASE, $thread->ID, $thread->post_author);
               $d = date_create($thread->post_date);
               $d = date_format($d, 'd-m-Y H:i:s');
               echo '<tr>';
                    echo sprintf('<td class="%s">%s</td>', $style, esc_html($thread->ID));
                    echo sprintf('<td class="%s">%s</td>', $style, esc_html($thread->post_title));
                    echo sprintf('<td class="%s">%s</td>', $style, esc_html($thread->post_excerpt));
                    echo sprintf('<td class="%s">%s</td>', $style, esc_html($d));
                    echo sprintf('<td class="%s">%s</td>', $style, esc_html($thread->post_author));
                    echo sprintf('<td class="%s"><a href="%s">%s</a></td>', $style, $href1st, $edit);
                    echo sprintf('<td class="%s"><a href="%s">%s</a></td>', $style, $href2nd, $delete);
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
                         <th>{$edit}</th>
                         <th>{$delete}</th>
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

     $headline = esc_html(__('Book Builder Thread', 'bookbuilder'));
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

     // print_r($thread);
     // foreach($thread->post_content['book'] as $item){
     foreach($thread->post_content['history'] as $item){

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

     switch($_REQUEST['action']){

          case 'delete':
               $survey_id = $_REQUEST['survey_id'];
               if(!is_null($survey_id)){
                    delete_survey_by_id($survey_id);
               }
               build_questionnaire_list_view();
               break;

          case 'edit':
               build_questionnaire_edit_view();
               break;
 
          default:
               build_questionnaire_list_view();
               break;
     }
}

add_shortcode('questionnaire_edit_view', 'build_questionnaire_edit_view');
function build_questionnaire_edit_view(){

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
     $redirect = esc_html(__('Redirect', 'bookbuilder'));
     $save_input = esc_html(__('Save', 'bookbuilder'));
     $apply = esc_html(__('Apply', 'bookbuilder'));
     $no_redirect = esc_html(__('No Redirect', 'bookbuilder'));
     $spread_view = esc_html(__('Spreads', 'bookbuilder'));
     $yes = esc_html(__('Yes', 'bookbuilder'));
     $no = esc_html(__('No', 'bookbuilder'));

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
                    <!-- <th>{$excerpt}</th> -->
                    <th>{$date}</th>
                    <th>{$parent}</th>
                    <th>{$title}</th>
                    <th>{$redirect}</th>
                    <th>{$spread_view}</th>
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
          <option value='default4th'>Default Layout Group 4th</option>
          <option value='default5th'>Default Layout Group 5th</option>
EOD;

// list of surveys
     $survey_titles = get_survey_titles();
     array_unshift($survey_titles, (object)['ID'=>'no_redirect', 'post_title'=>$no_redirect]);

     foreach($coll as $question){

// the question
          $question->post_content = pagpick($question->post_content);
          $question_id = $question->ID;

// group type question
          $node_style = '';
          $rule_input = '';
          if('group' == $question->post_content['type']){
               $node_style = 'group-title';
          }

// redirect reference
          $buf = '';
          foreach($survey_titles as $item){
               $selected = '';
               if($item->ID == $question->post_content['redirect_survey_id']){
                    $selected = 'selected';
               }
               $buf.= sprintf('<option name="redirect" value="%s" %s>%s</option>', esc_html($item->ID), esc_html($selected), esc_html($item->post_title));
          }
          $js = 'javascript:setRedirect(this);';
          $link_input = sprintf('<select class="select-redirect question-%s" onchange="%s">%s</select>', $question->ID, $js, $buf);

// spread view
          $buf = '';
          $selected = '';

          if('true' == $question->post_content['show_spread_state']){
               $selected = 'selected';
          }

          $buf.= sprintf('<option name="spread_view" value="%s" %s>%s</option>', 'false', esc_html($selected), esc_html($no));
          $buf.= sprintf('<option name="spread_view" value="%s" %s>%s</option>', 'true', esc_html($selected), esc_html($yes));
          $js = 'javascript:setShowSpreadState(this);';
          $spread_input = sprintf('<select class="select-spread_view question-%s" onchange="%s">%s</select>', $question->ID, $js, $buf);

// parent group of the question
          $parent = '';
          if(!is_null($question->post_content['conf']['parent'])){
               $parent = $question->post_content['conf']['parent'];
          }

// post date
          $d = date_create($question->post_date);
          $d = date_format($d, 'd.m.Y H:i:s');

// output
          echo '<tr>';
          echo sprintf('<td>%s</td>', esc_html($question->ID));
          // echo sprintf('<td>%s</td>', esc_html($question->post_excerpt));
          echo sprintf('<td>%s</td>', esc_html($d));
          echo sprintf('<td>%s</td>', esc_html($parent));
          echo sprintf('<td class="%s">%s</td>', $node_style, esc_html($question->post_content['title']));
          echo sprintf('<td>%s</td>', $link_input);
          echo sprintf('<td>%s</td>', $spread_input);
          echo '</tr>';
     }

     echo <<<EOD
          <tfoot>
               <tr>
                    <th>{$id}</th>
                    <!-- <th>{$excerpt}</th> //-->
                    <th>{$date}</th>
                    <th>{$parent}</th>
                    <th>{$title}</th>
                    <th>{$redirect}</th>
                    <th>{$spread_view}</th>
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
     $edit = esc_html(__('Edit', 'bookbuilder'));
     $edit_fields = esc_html(__('Edit Fields', 'bookbuilder'));
     $delete = esc_html(__('Delete', 'bookbuilder'));
     $delete_survey = esc_html(__('Delete Questionnaire', 'bookbuilder'));
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
                    <th>{$edit}</th>
                    <th>{$delete}</th>
               </tr>
          </thead>
EOD;

     $style = 'column-primary';
     if(!is_null($coll[0])){
          foreach($coll as $survey){
               $edit_link = sprintf('%s?page=questionnaire&action=edit&survey_id=%s', Path::SERVICE_BASE, $survey->ID);
               $delete_link = sprintf('%s?page=questionnaire&action=delete&survey_id=%s', Path::SERVICE_BASE, $survey->ID);
               $d = date_create($survey->post_date);
               $d = date_format($d, 'd-m-Y H:i:s');
               echo '<tr>';
                    echo sprintf('<td class="%s">%s</td>', $style, esc_html($survey->ID));
                    echo sprintf('<td class="%s">%s</td>', $style, esc_html($survey->post_title));
                    echo sprintf('<td class="%s">%s</td>', $style, esc_html($survey->post_name));
                    echo sprintf('<td class="%s">%s</td>', $style, esc_html($d));
                    echo sprintf('<td class="%s"><a href="%s">%s</a></td>', $style, $edit_link, $edit_fields);
                    echo sprintf('<td class="%s"><a href="%s">%s</a></td>', $style, $delete_link, $delete_survey);
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
                    <th>{$edit}</th>
                    <th>{$delete}</th>
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
