<?php defined('ABSPATH') || exit;



// inserts menu items of the plugin
add_action('admin_menu', 'bb_setup_admin_menu');
function bb_setup_admin_menu() {

     $page_title = 'surveyprint';
     $menu_title = esc_html(__('BookBuilder', 'bookbuilder'));
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
     $function = 'bb_build_surveyprint_utils_view';
     add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);

     $parent_slug = 'surveyprint_admin_utils';
     $menu_title = esc_html(__('Typeform Utilities', 'survey'));
     $page_title = 'utils';
     $menu_slug = 'typeform_utils';
     $capability = 'administrator';
     $function = 'bb_build_typeform_utils_view';
     add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);

     $parent_slug = 'surveyprint_admin_utils';
     $menu_title = esc_html(__('Stored Questionnaire', 'survey'));
     $page_title = 'questionnaire';
     $menu_slug = 'questionnaire';
     $capability = 'administrator';
     $function = 'bb_build_questionnaire_view';
     add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);

     $parent_slug = 'surveyprint_admin_utils';
     $menu_title = esc_html(__('Client Threads', 'survey'));
     $page_title = 'threads';
     $menu_slug = 'threads';
     $capability = 'administrator';
     $function = 'bb_build_thread_view';
     add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);

     $parent_slug = 'surveyprint_admin_utils';
     $menu_title = esc_html(__('Books', 'survey'));
     $page_title = 'spreads';
     $menu_slug = 'spreads';
     $capability = 'administrator';
     $function = 'bb_build_book_utils_view';
     add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);

     $parent_slug = 'surveyprint_admin_utils';
     $page_title = 'layouts';
     $menu_slug = 'layouts';
     $capability = 'administrator';
     $menu_title = esc_html(__('Layouts', 'survey'));
     $function = 'bb_build_layouts_view';
     add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);

     remove_submenu_page('surveyprint_admin_utils', 'surveyprint_admin_utils');
};



//  book utilities
add_shortcode('bb_book_utils_view', 'bb_build_book_utils_view');
function bb_build_book_utils_view(){
}



// surveyprint utilities
add_shortcode('bb_surveyprint_utils_view', 'bb_build_surveyprint_utils_view');
function bb_build_surveyprint_utils_view(){

     wp_register_script('service', Path::get_plugin_url().'/js/admin/main.js', array('jquery'));
     wp_register_script('service_i18n', Path::get_plugin_url().'/js/admin/i18n.js');

     wp_enqueue_script('service_i18n');
     wp_enqueue_script('service');

     $headline = esc_html(__('BookBuilder Plugin Utilities', 'bookbuilder'));
     $welcome = esc_html(__(':', 'bookbuilder'));
     $delete_db = esc_html(__('Removal of the BookBuider DB', 'bookbuilder'));
     $init_db = esc_html(__('Initialization of the BookBuilder DB', 'bookbuilder'));
     $init_page = esc_html(__('Initialization of the BookBuilder Page', 'bookbuilder'));

     echo <<<EOD
          <div class='wrap'>
               <h1 class='wp-heading-inline'>{$headline}</h1>
               <div class='page-title-action messages'><span>{$welcome}</span></div>
               <hr class='wp-header-end'>
     EOD;

     echo <<<EOD
     <div class='edit'>
          <div class='unit'><a href='javascript:BBAdmin.bbDeleteDB();'>{$delete_db}</a></div>
          <div class='unit'><a href='javascript:BBAdmin.bbInitDB();'>{$init_db}</a></div>
          <div class='unit'><a href='javascript:BBAdmin.bbInitPage();'>{$init_page}</a></div>
     </div>
EOD;

}



// typeform utilities
add_shortcode('bb_typeform_utils_view', 'bb_build_typeform_utils_view');
function bb_build_typeform_utils_view(){

     wp_register_script('service', Path::get_plugin_url().'/js/admin/main.js', array('jquery'));
     wp_register_script('service_i18n', Path::get_plugin_url().'/js/admin/i18n.js');

     wp_enqueue_script('service_i18n');
     wp_enqueue_script('service');

     $headline = esc_html(__('BookBuilder Typeform Utilities', 'bookbuilder'));
     $welcome = esc_html(__(':', 'bookbuilder'));
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
     $construction = esc_html(__('Construction of the Questionnaires', 'bookbuilder'));

     echo <<<EOD
     <div class='edit'>
          <div class='unit'>{$auth_token}</div>
          <div class='unit'><input class='auth_token' type='text'></input></div>
          <div class='unit'>{$bucket_name}</div>
          <div class='unit'><input class='bucket' type='text' value='N2BwhIXs'></input></div>
          <div class='unit'><input class='filename' type='text' value='typeform_survey.json'></input></div>
          <div class='unit'>{$actions}</div>
          <div class='unit'><a href='javascript:BBAdmin.bbInsertTypeformSurveys();'>{$construction}</a></div>
     </div>
EOD;

}



add_shortcode('bb_thread_view', 'bb_build_thread_view');
function bb_build_thread_view(){

     switch($_REQUEST['action']){

          case 'edit':
               bb_build_thread_entries_view();
               break; 

          case 'delete':
               $thread_id = $_REQUEST['thread_id'];
               $client_id = $_REQUEST['client_id'];
               if(!is_null($thread_id) && !is_null($thread_id)){
                    delete_thread_by_id($thread_id, $client_id);
               }
               bb_build_thread_list_view();
               break;

          default:
               bb_build_thread_list_view();
               break;
     }
}



add_shortcode('bb_thread_list_view', 'bb_build_thread_list_view');
function bb_build_thread_list_view(){

     $headline = esc_html(__('List of the Threads', 'bookbuilder'));
     $welcome = esc_html(__('', 'bookbuilder'));

     wp_register_style('admin_style', Path::get_plugin_url().'/css/admin/style.css');
     wp_enqueue_style('admin_style');

     $headline = esc_html(__('BookBuilder Client Threads', 'bookbuilder'));
     $id = esc_html(__('ID', 'bookbuilder'));
     $title = esc_html(__('Title', 'bookbuilder'));
     $excerpt = esc_html(__('Type', 'bookbuilder'));
     $date = esc_html(__('Date of Init', 'bookbuilder'));
     $author = esc_html(__('Author', 'bookbuilder'));
     $edit = esc_html(__('Edit', 'bookbuilder'));
     $edit_thread = esc_html(__('Edit Thread', 'bookbuilder'));
     $delete = esc_html(__('Delete', 'bookbuilder'));
     echo <<<EOD
          <div class='wrap'>
               <h1 class='wp-heading-inline'>{$headline}</h1>
               <div class='page-title-action messages'><span>{$welcome}</span></div>
               <hr class='wp-header-end'>

EOD;

}



add_shortcode('bb_thread_entries_view', 'bb_build_thread_entries_view');
function bb_build_thread_entries_view(){

     wp_register_style('admin_style', Path::get_plugin_url().'/css/admin/style.css');
     wp_enqueue_style('admin_style');

     $thread_id = trim_incoming_numeric($_REQUEST['thread_id']);
     $client_id = trim_incoming_numeric($_REQUEST['client_id']);

     $thread = get_thread_by_id($client_id, $thread_id)[0];

     if(is_null($thread)){ return false; }
     $thread->post_content = pagpick($thread->post_content);

     // $toc = get_toc_by_thread_id($thread_id, $client_id)[0];
     // if(is_null($toc)){ return false; }
     // $toc->post_content = pagpick($toc->post_content);

     $sections = get_sections_by_thread_id($thread_id, $client_id);
     if(is_null($sections)){ return false; }

     $headline = esc_html(__('BookBuilder Stored Threads', 'bookbuilder'));
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
     $welcome = esc_html(__('', 'bookbuilder'));
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

add_shortcode('bb_questionnaire_view', 'bb_build_questionnaire_view');
function bb_build_questionnaire_view(){

     switch($_REQUEST['action']){

          case 'delete':
               $survey_id = $_REQUEST['survey_id'];
               if(!is_null($survey_id)){
                    delete_survey_by_id($survey_id);
               }
               bb_build_questionnaire_list_view();
               break;

          case 'edit':
               bb_build_questionnaire_edit_view();
               break;
 
          default:
               bb_build_questionnaire_list_view();
               break;
     }
}

add_shortcode('bb_questionnaire_edit_view', 'bb_build_questionnaire_edit_view');
function bb_build_questionnaire_edit_view(){

     $surveys = bb_get_typeform_surveys();
     $survey_ref = $_REQUEST['ref'];
     $survey = bb_get_survey_by_ref($survey_ref);

     wp_register_style('admin_style', Path::get_plugin_url().'/css/admin/style.css');
     wp_enqueue_style('admin_style');
 
     wp_register_script('service', Path::get_plugin_url().'/js/admin/main.js', array('jquery'));
     wp_register_script('service_i18n', Path::get_plugin_url().'/js/admin/i18n.js');

     wp_enqueue_script('service');
     wp_enqueue_script('service_i18n');

     $welcome = esc_html(__(':', 'bookbuilder'));
     $headline = esc_html(__('BookBuilder Stored Questionnaire', 'bookbuilder'));

     echo <<<EOD

          <div class='wrap'>
               <h1 class='wp-heading-inline'>{$headline}</h1>
               <div class='page-title-action messages'><span>{$welcome}</span></div>
               <hr class='wp-header-end'>
EOD;

     $field_select = <<<EOD
           <div class='blockR'>
                <select onchange='javascript:BBAdmin.bbSelectTargetField(this);'>
                     <option>field</option>
                </select>
           </div>
EOD;

     $field_out = <<<EOD
          <div class='blockL'>%s</div>
EOD;

     foreach($survey['fields'] as $field){

          $buf1st = '';

          switch($field->type){

               case 'yes_no':
               case 'multiple_choice':
               case 'number':
               case 'short_text':
               case 'statement':

                    foreach($field->choices as $choice){

                         $buf2nd = "<option value=''>No Survey</option>";

                         foreach($surveys as $target){

                              $selected = '';
                              if($target->ref == $choice->target_survey_ref){
                                   $selected = 'selected';
                              }

                              $buf2nd.= sprintf("<option value='%s' %s>%s</option>", $target->ref, $selected, $target->title);
                         };

                         $buf1st.= "<div class='choice-output row'>";
                         $buf1st.= sprintf($field_out, $choice->title);

                         $buf1st.= "<div class='blockR'>";
                         $buf1st.= sprintf("<select class='bind:%s' onchange='javascript:BBAdmin.bbSelectTargetSurvey(this);'>", $choice->ref);
                         $buf1st.= $buf2nd;
                         $buf1st.= "</select>";
                         $buf1st.= '</div>';

                         $buf1st.= $field_select;

                         $buf1st.= '</div>';
                    }

                    break;
          }

          echo <<<EOD
               <div class='field-output'>
                    {$field->title}
                    {$buf1st}
               </div>
EOD;

     }

}

add_shortcode('bb_questionnaire_list_view', 'bb_build_questionnaire_list_view');
function bb_build_questionnaire_list_view(){

     wp_register_style('admin_style', Path::get_plugin_url().'/css/admin/style.css');
     wp_enqueue_style('admin_style');
 
     wp_register_script('service_i18n', Path::get_plugin_url().'/js/admin/i18n.js');
     wp_enqueue_script('service_i18n');

     wp_register_script('service', Path::get_plugin_url().'/js/admin/main.js', array('jquery'));
     wp_enqueue_script('service');

     $message = esc_html(__('List of stored Questionnaire:', 'bookbuilder'));
     $id = esc_html(__('ID', 'bookbuilder'));
     $excerpt = esc_html(__('Reference', 'bookbuilder'));
     $title = esc_html(__('Title', 'bookbuilder'));
     $date = esc_html(__('Date of Init', 'bookbuilder'));
     $edit = esc_html(__('Edit', 'bookbuilder'));
     $edit_fields = esc_html(__('Edit Fields', 'bookbuilder'));
     $delete = esc_html(__('Delete', 'bookbuilder'));
     $delete_survey = esc_html(__('Delete Questionnaire', 'bookbuilder'));
     $headline = esc_html(__('BookBuilder Stored Questionnaire', 'bookbuilder'));
     $welcome = esc_html(__(':', 'bookbuilder'));

     echo <<<EOD

          <div class='wrap'>
               <h1 class='wp-heading-inline'>{$headline}</h1>
               <div class='page-title-action messages'><span>{$welcome}</span></div>
               <hr class='wp-header-end'>
EOD;

     $surveys = bb_get_typeform_surveys();



     $plz_select_root_survey = esc_html(__('The Survey to start with', 'bookbuilder'));
     $buf1st = sprintf("<option value=''>%s</option>", $plz_select_root_survey);
     foreach($surveys as $survey){
          $selected = '';
          $buf1st.= sprintf("<option value='%s' %s>%s</option>", $survey->title, $selected, $survey->title);
     }
     echo <<<EOD
                <select onchange='javascript:BBAdmin.bbSelectRootSurvey(this);'>
                     {$buf1st}
                </select>
           </div>
EOD;



     foreach($surveys as $survey){
     $href = sprintf('%s?page=questionnaire&action=edit&ref=%s', Path::SERVICE_BASE, $survey->ref);
     echo <<<EOD
          <div class='survey-output'>
              <a href='{$href}'>{$survey->title}</a>
          </div>
EOD;
     }
}



add_shortcode('bb_layouts_view', 'bb_build_layouts_view');
function bb_build_layouts_view() {

     wp_register_script(     'viewer-config', Path::get_plugin_url().'/js/spread/src/main/config-admin.js');
     wp_register_script(       'viewer-main', Path::get_plugin_url().'/js/spread/src/main/main.js');
     wp_register_script(      'viewer-tools', Path::get_plugin_url().'/js/spread/src/main/module/tools/main.js');
     wp_register_script(     'viewer-screen', Path::get_plugin_url().'/js/spread/src/main/module/screen/main.js');
     wp_register_script(    'viewer-correct', Path::get_plugin_url().'/js/spread/src/main/module/screen/correct.js');
     wp_register_script(     'viewer-bitmap', Path::get_plugin_url().'/js/spread/src/main/module/screen/bitmap.js');
     wp_register_script(        'viewer-svg', Path::get_plugin_url().'/js/spread/lib/svg.js');
     wp_register_script('viewer-layout_util', Path::get_plugin_url().'/js/spread/src/main/module/util/main.js');
     wp_register_script( 'viewer-layout_net', Path::get_plugin_url().'/js/spread/src/main/module/net/main.js');
     wp_register_script('viewer-layout_init', Path::get_plugin_url().'/js/spread/init.js', array('jquery'));

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

     wp_register_style('viewer_style', Path::get_plugin_url().'/css/spread/style.css');
     wp_enqueue_style('viewer_style');

     $headline = esc_html(__('BookBuilder Layout Zentrale', 'bookbuilder'));
     $welcome = esc_html(__('', 'bookbuilder'));
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



