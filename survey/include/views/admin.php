<?php defined('ABSPATH') || exit;

DEFINE('SERVICE_BASE', '/wp-admin/admin.php');

// plugin menu insert
add_action('admin_menu', 'setup_admin_menu');
function setup_admin_menu() {

     $page_title = 'surveyprint';
     $menu_title = esc_html(__('SurveyPrint', 'nosuch'));
     $menu_slug = 'surveyprint_admin_utils';
     $capability = 'administrator';
     $function = '';
     $icon_url = '';
     $position = '25';
     add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position); 

     $parent_slug = 'surveyprint_admin_utils';
     $page_title = 'utils';
     $menu_slug = 'surveyprint_utils';
     $capability = 'administrator';
     $menu_title = esc_html(__('SurveyPrint Utilities', 'survey'));
     $function = 'build_surveyprint_utils_view';
     add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);

     $parent_slug = 'surveyprint_admin_utils';
     $page_title = 'utils';
     $menu_slug = 'typeform_utils';
     $capability = 'administrator';
     $menu_title = esc_html(__('Typeform Utilities', 'survey'));
     $function = 'build_typeform_utils_view';
     add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);

     $parent_slug = 'surveyprint_admin_utils';
     $page_title = 'questionnaire';
     $menu_slug = 'questionnaire';
     $capability = 'administrator';
     $menu_title = esc_html(__('Stored Questionnaire', 'survey'));
     $function = 'build_questionnaire_view';
     add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);

     $parent_slug = 'surveyprint_admin_utils';
     $page_title = 'threads';
     $menu_slug = 'threads';
     $capability = 'administrator';
     $menu_title = esc_html(__('Threads', 'survey'));
     $function = 'build_thread_view';
     add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);
/*
     $parent_slug = 'surveyprint_admin_utils';
     $page_title = 'spreads';
     $menu_slug = 'spreads';
     $capability = 'administrator';
     $menu_title = esc_html(__('Spread Manager', 'survey'));
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

     wp_register_script('service_i18n',    WP_PLUGIN_URL.SURVeY.'/js/services/i18n.js');
     wp_register_script('service',         WP_PLUGIN_URL.SURVeY.'/js/services/admin.js', array('jquery'));
     wp_enqueue_script('service_i18n');
     wp_enqueue_script('service');

     $headline = esc_html(__('SurveyPrint Utilities', 'nosuch'));
     $welcome = esc_html(__('Welcome', 'nosuch'));
     echo <<<EOD
          <div class='wrap'>
               <h1 class='wp-heading-inline'>{$headline}</h1>
               <div class='page-title-action messages'><span>{$welcome}</span></div>
               <hr class='wp-header-end'>
     EOD;

     $actions = esc_html(__('Actions:', 'nosuch'));
     $constructFieldingQuestions = esc_html(__('Construciton of the Fielding Questions', 'nosuch'));
     $edit = esc_html(__('Edit of a Questionnaire', 'nosuch'));
     echo <<<EOD
     <div class='edit'>
          <div class='unit'>{$actions}</div>
          <div><a href='javascript:constructFieldingQuestions();'>{$constructFieldingQuestions}</a></div>
     </div>
EOD;

}

// typeform utilities
add_shortcode('typeform_utils_view', 'build_typeform_utils_view');
function build_typeform_utils_view(){

     wp_register_script('service_i18n',    WP_PLUGIN_URL.SURVeY.'/js/services/i18n.js');
     wp_register_script('service',         WP_PLUGIN_URL.SURVeY.'/js/services/admin.js', array('jquery'));
     wp_enqueue_script('service_i18n');
     wp_enqueue_script('service');

     $headline = esc_html(__('Typeform Survey Utilities', 'nosuch'));
     $welcome = esc_html(__('Welcome', 'nosuch'));
     echo <<<EOD
          <div class='wrap'>
               <h1 class='wp-heading-inline'>{$headline}</h1>
               <div class='page-title-action messages'><span>{$welcome}</span></div>
               <hr class='wp-header-end'>
     EOD;

     $auth_token = esc_html(__('Typeform Auth Token:', 'nosuch'));
     $download_survey = esc_html(__('Download of the Typeform Questionnaire', 'nosuch'));
     $actions = esc_html(__('Actions:', 'nosuch'));
     $bucket_name = esc_html(__('Bucket name:', 'nosuch'));
     $download_resultset = esc_html(__('Download of a Typeform Resultset', 'nosuch'));
     $construction = esc_html(__('Construciton of a Questionnaire', 'nosuch'));
     $edit = esc_html(__('Edit of a Questionnaire', 'nosuch'));
     echo <<<EOD
     <div class='edit'>
          <div class='unit'>{$auth_token}</div>
          <div><input class='auth_token' type='text'></input></div>
          <div class='unit'>{$bucket_name}</div>
          <div><input class='bucket' type='text' value='N2BwhIXs'></input></div>
          <div class='unit'>{$actions}</div>
          <div><a href='javascript:downloadTypeformSurvey();'>{$download_survey}</a></div>
          <div><a href='javascript:downloadTypeformSurveyResult();'>{$download_resultset}</a></div>
          <div><a href='javascript:constructTypeformSurvey();'>{$construction}</a></div>
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

     $headline = esc_html(__('List of the Threads of some Client', 'nosuch'));
     $welcome = esc_html(__('Todo', 'nosuch'));

     wp_register_style('admin_style', WP_PLUGIN_URL.SURVeY.'/css/admin/style.css');
     wp_enqueue_style('admin_style');

     $headline = esc_html(__('Threads', 'nosuch'));
     $id = esc_html(__('ID', 'nosuch'));
     $title = esc_html(__('Title', 'nosuch'));
     $excerpt = esc_html(__('Type', 'nosuch'));
     $date = esc_html(__('Date of Init', 'nosuch'));
     $author_id = esc_html(__('Author', 'nosuch'));
     $action = esc_html(__('Action', 'nosuch'));
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

     $edit = esc_html(__('Entries', 'nosuch'));
     $style = 'column-primary';
     $coll = get_threads();
     if(!is_null($coll[0])){
          foreach($coll as $thread){
               $href = sprintf('%s?page=threads&edit=entries&thread_id=%s', SERVICE_BASE, $thread->ID);
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

     $thread_id = $_REQUEST['thread_id'];
     $coll = get_toc_by_thread_id($thread_id)[0];
     if(is_null($coll)){
          return false;
     }
     $coll->post_content = pagpick($coll->post_content);

     $headline = esc_html(__('Threads', 'nosuch'));
     $id = esc_html(__('ID', 'nosuch'));
     $title = esc_html(__('Title', 'nosuch'));
     $excerpt = esc_html(__('Type', 'nosuch'));
     $date = esc_html(__('Date of Init', 'nosuch'));
     $author_id = esc_html(__('Author', 'nosuch'));
     $action = esc_html(__('Action', 'nosuch'));
     $question = esc_html(__('Question', 'nosuch'));
     $answer = esc_html(__('Answer', 'nosuch'));
     $edit = esc_html(__('Edit', 'nosuch'));
     $assits = esc_html(__('Assets', 'nosuch'));
     $welcome = esc_html(__('Welcome', 'nosuch'));

     echo <<<EOD
          <div class='wrap'>
               <h1 class='wp-heading-inline'>{$headline}</h1>
               <div class='page-title-action messages'><span>{$welcome}</span></div>
               <hr class='wp-header-end'>

               <table class="wp-list-table widefat striped table-view-list posts">
               <thead>
                    <tr>
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
     if(null == $coll->post_content['walkytalky']){ $coll->post_content['walkytalky'] = []; }
     foreach($coll->post_content['walkytalky'] as $ref){
          $panel = get_panel_by_ref($thread_id, $ref)[0];
          $panel->post_content = pagpick($panel->post_content);
          $assets = get_assets_by_panel_ref($thread_id, $panel->post_excerpt);
          $buf = '';
          foreach($assets as $asset){
               $buf.= sprintf('<img width="75px" src="%s">', add_base_to_chunk($asset->post_content));
          }
          // $href = sprintf('%s?page=threads&edit=entries&thread_id=%s', SERVICE_BASE, $thread->ID);
          $href = '#';;
          $d = date_create($survey->post_date);
          $d = date_format($d, 'd-m-Y H:i:s');
          echo '<tr>';
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
          default:
               build_questionnaire_list_view();
               break;
     }
}

add_shortcode('question_view', 'build_question_view');
function build_question_view(){

     wp_register_script('service', WP_PLUGIN_URL.SURVeY.'/js/services/admin.js', array('jquery'));
     wp_register_script('service_i18n',    WP_PLUGIN_URL.SURVeY.'/js/services/i18n.js');
     wp_enqueue_script('service');
     wp_enqueue_script('service_i18n');

     $message = esc_html(__('List of Questions', 'nosuch'));
     $id = esc_html(__('ID', 'nosuch'));
     $title = esc_html(__('Question', 'nosuch'));
     $max_assets = esc_html(__('Num of Max Assets', 'nosuch'));
     $headline = esc_html(__('Questionnaire', 'nosuch'));
     $welcome = esc_html(__('Welcome', 'nosuch'));
     $layout_group = esc_html(__('Layout Group', 'nosuch'));
     $date = esc_html(__('Date of Init', 'nosuch'));
     echo <<<EOD
          <div class='wrap'>
               <h1 class='wp-heading-inline'>{$headline}</h1>
               <div class='page-title-action messages'><span>{$welcome}</span></div>
               <hr class='wp-header-end'>
          <table class="wp-list-table widefat striped table-view-list posts">
          <thead>
               <tr>
                    <th>{$id}</th>
                    <th>{$date}</th>
                    <th>{$title}</th>
                    <th>{$layout_group}</th>
                    <th>{$max_assets}</th>
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

     $survey_id = $_REQUEST['survey_id'];
     $coll = get_questions_by_survey_id($survey_id);
     foreach($coll as $question){
          $d = date_create($question->post_date);
          $d = date_format($d, 'd.m.Y H:i:s');
          echo '<tr>';
          echo sprintf('<td>%s</td>', esc_html($question->ID));
          echo sprintf('<td>%s</td>', esc_html($d));
          echo sprintf('<td>%s</td>', esc_html($question->post_title));
          echo sprintf('<td><select class="layout-group-select lsel-%s" question_id="%s">%s</select></td>', $question->ID, $question->ID, $layout_options);
          echo sprintf('<td><select class="max-asset-select msel-%s" question_id="%s">%s</select></td>', $question->ID, $question->ID, $asset_options);
          echo '</tr>';
     }

     $dp = json_encode($coll);

     echo <<<EOD
          <tfoot>
               <tr>
                    <th>{$id}</th>
                    <th>{$date}</th>
                    <th>{$title}</th>
                    <th>{$layout_group}</th>
                    <th>{$max_assets}</th>
               </tr>
          </tfoot>
          </table>

          <script type='text/javascript'>
               let ref = this;
               let coll = {$dp};
               jQuery(document).ready(function(e){
                    for(let idx in coll){
                         let ct = jQuery.parseJSON(atob(coll[idx].post_content));
                         let id = coll[idx].ID
                         jQuery('.msel-'+id).val(ct.conf.max_assets);
                         jQuery('.lsel-'+id).val(ct.conf.layout_group);
                    }
                    jQuery('.max-asset-select').change(function(){
                         let questionId = jQuery(this).attr('question_id');
                         let max = this.value;
                         let group = null;
                         ref.saveQuestion(questionId, max, group);
                    });
                    jQuery('.layout-group-select').change(function(){
                         let questionId = jQuery(this).attr('question_id');
                         let max = null;
                         let group = this.value;
                         ref.saveQuestion(questionId, max, group);
                    });
               });
          </script>

EOD;

}

add_shortcode('questionnaire_list_view', 'build_questionnaire_list_view');
function build_questionnaire_list_view(){

     $message = esc_html(__('List of stored Questionnaire:', 'nosuch'));
     $id = esc_html(__('ID', 'nosuch'));
     $excerpt = esc_html(__('Reference', 'nosuch'));
     $title = esc_html(__('Title', 'nosuch'));
     $date = esc_html(__('Date of Init', 'nosuch'));
     $action = esc_html(__('Action', 'nosuch'));
     $edit = esc_html(__('Questions', 'nosuch'));

     $headline = esc_html(__('Questionnaire', 'nosuch'));
     $welcome = esc_html(__('Welcome', 'nosuch'));
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
     $coll = get_surveys();
     if(!is_null($coll[0])){
          foreach($coll as $survey){
               $href = sprintf('%s?page=questionnaire&edit=questions&survey_id=%s', SERVICE_BASE, $survey->ID);
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

     wp_register_script('viewer-config',       WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/src/main/config-spreads.js');
     wp_register_script('viewer-main',         WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/src/main/main.js');
     wp_register_script('viewer-tools',        WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/src/main/module/tools/main.js');
     wp_register_script('viewer-screen',       WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/src/main/module/screen/main.js');
     wp_register_script('viewer-correct',      WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/src/main/module/screen/correct.js');
     wp_register_script('viewer-bitmap',       WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/src/main/module/screen/bitmap.js');
     wp_register_script('viewer-svg',          WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/lib/svg.js');
     wp_register_script('viewer-layout_util',  WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/src/main/module/util/main.js');
     wp_register_script('viewer-layout_net',   WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/src/main/module/net/main.js');
     wp_register_script('viewer-layout_init',  WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/init.js', array('jquery'));

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

     $headline = esc_html(__('Spread Manager', 'nosuch'));
     $welcome = esc_html(__('Welcome', 'nosuch'));
     $res = <<<EOD
     <div class='wrap'>
          <h1 class='wp-heading-inline'>{$headline}</h1>
          <div class='page-title-action'><span>{$welcome}</span></div>
          <hr class='wp-header-end'>
          <div class='layout-edit'>
               <div class='layout-messages'></div>
               <div class='layout-main'>
                    <div class='layout-rows'>
                         <div class='layout-buff'><div class='screen'></div></div>
                    </div>
                    <div class='layout-controls'>
                         <div class='layout-pages'></div>
                         <div class='layout-tools'></div>
                         <div class='layout-library'></div>
                         <div class='layout-actions'></div>
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

     wp_register_script('viewer-config',       WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/src/main/config-layouts.js');
     wp_register_script('viewer-main',         WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/src/main/main.js');
     wp_register_script('viewer-tools',        WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/src/main/module/tools/main.js');
     wp_register_script('viewer-screen',       WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/src/main/module/screen/main.js');
     wp_register_script('viewer-correct',      WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/src/main/module/screen/correct.js');
     wp_register_script('viewer-bitmap',       WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/src/main/module/screen/bitmap.js');
     wp_register_script('viewer-svg',          WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/lib/svg.js');
     wp_register_script('viewer-layout_util',  WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/src/main/module/util/main.js');
     wp_register_script('viewer-layout_net',   WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/src/main/module/net/main.js');
     wp_register_script('viewer-layout_init',  WP_PLUGIN_URL.SURVeY.'/js/spread-viewer/init.js', array('jquery'));

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

     $headline = esc_html(__('Layout Manager', 'nosuch'));
     $welcome = esc_html(__('Welcome', 'nosuch'));
     $res = <<<EOD
     <div class='wrap'>
          <h1 class='wp-heading-inline'>{$headline}</h1>
          <div class='page-title-action'><span>{$welcome}</span></div>
          <hr class='wp-header-end'>
          <div class='layout-edit'>
               <div class='layout-messages'></div>
               <div class='layout-main'>
                    <div class='layout-rows'>
                         <div class='layout-buff'><div class='screen'></div></div>
                    </div>
                    <div class='layout-controls'>
                         <div class='layout-pages'></div>
                         <div class='layout-toolbar'></div>
                         <div class='layout-controlbar'></div>
                         <div class='layout-tools'></div>
                         <div class='layout-library'></div>
                         <div class='layout-actions'></div>
                    </div>
               </div>
          </div>
          <div class='offscreen'></div>
          <div class='printscreen'></div>
     </div>
EOD;
     echo $res;
}
