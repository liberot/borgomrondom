<?php defined('ABSPATH') || exit;

add_shortcode('survey_view', 'build_survey_view');
function build_survey_view(){

     wp_register_style('client_style', WP_PLUGIN_URL.'/nosuch/survey/css/web-client/style.css');
     wp_enqueue_style('client_style');

     wp_register_script(     'config', WP_PLUGIN_URL.'/nosuch/survey/js/web-client/src/main/config-client.js', ['jquery']);
     wp_enqueue_script(      'config');

     if(!is_user_logged_in()){
          echo '<p>View runs dev ProfileBuilder authentication procedere<br/>u: surveyprint <br/>p: surveyprint</p>';
          echo do_shortcode('[wppb-login]');
          echo do_shortcode('[wppb-register]');
          echo do_shortcode('[wppb-recover-password]');
          return;
     }

     wp_register_script(       'main', WP_PLUGIN_URL.'/nosuch/survey/js/web-client/src/main/main.js');
     wp_register_script(     'client', WP_PLUGIN_URL.'/nosuch/survey/js/web-client/src/main/survey.js');
     wp_register_script(        'net', WP_PLUGIN_URL.'/nosuch/survey/js/web-client/src/main/module/net/main.js');
     wp_register_script('client_util', WP_PLUGIN_URL.'/nosuch/survey/js/web-client/src/main/module/util/main.js');
     wp_register_script('client_i18n', WP_PLUGIN_URL.'/nosuch/survey/js/web-client/src/main/module/i18n/main.js');
     wp_register_script(     'base64', WP_PLUGIN_URL.'/nosuch/survey/js/web-client/lib/base64.js');
     wp_register_script('client_init', WP_PLUGIN_URL.'/nosuch/survey/js/web-client/init.js');
     wp_enqueue_script( 'client_i18n');
     wp_enqueue_script(        'main');
     wp_enqueue_script(      'client');
     wp_enqueue_script(         'net');
     wp_enqueue_script( 'client_util');
     wp_enqueue_script( 'client_init');
     wp_enqueue_script(      'base64');

     $res = <<<EOD
     <div class='survey'>
          <div class='survey-messages'></div>
          <div class='survey-list'></div>
          <div class='survey-thread'></div>
               <div class='survey-controls1st'></div>
               <div class='survey-controls5th'></div>
               <div class='survey-questions1st'></div>
               <div class='file-upload'></div>
               <div class='survey-controls2nd'></div>
               <div class='survey-controls3rd'></div>
               <div class='survey-controls4th'></div>
               <div class='survey-assets'></div>
          </div>
     </div>

EOD;
     echo $res;
}

add_shortcode('constructor_view', 'build_constructor_view');
function build_constructor_view() {

     wp_register_script(     'viewer-config', WP_PLUGIN_URL.'/nosuch/survey/js/spread-viewer/src/main/config-client.js');
     wp_register_script(              'main', WP_PLUGIN_URL.'/nosuch/survey/js/spread-viewer/src/main/main.js');
     wp_register_script(      'viewer-tools', WP_PLUGIN_URL.'/nosuch/survey/js/spread-viewer/src/main/module/tools/main.js');
     wp_register_script(     'viewer-screen', WP_PLUGIN_URL.'/nosuch/survey/js/spread-viewer/src/main/module/screen/main.js');
     wp_register_script(    'viewer-correct', WP_PLUGIN_URL.'/nosuch/survey/js/spread-viewer/src/main/module/screen/correct.js');
     wp_register_script(     'viewer-bitmap', WP_PLUGIN_URL.'/nosuch/survey/js/spread-viewer/src/main/module/screen/bitmap.js');
     wp_register_script(        'viewer-svg', WP_PLUGIN_URL.'/nosuch/survey/js/spread-viewer/lib/svg.js');
     wp_register_script('viewer-layout_util', WP_PLUGIN_URL.'/nosuch/survey/js/spread-viewer/src/main/module/util/main.js');
     wp_register_script( 'viewer-layout_net', WP_PLUGIN_URL.'/nosuch/survey/js/spread-viewer/src/main/module/net/main.js');
     wp_register_script('viewer-layout_init', WP_PLUGIN_URL.'/nosuch/survey/js/spread-viewer/init.js', array('jquery'));

     wp_enqueue_script('viewer-config');
     wp_enqueue_script('main');
     wp_enqueue_script('viewer-tools');
     wp_enqueue_script('viewer-bitmap');
     wp_enqueue_script('viewer-correct');
     wp_enqueue_script('viewer-screen');
     wp_enqueue_script('viewer-svg');
     wp_enqueue_script('viewer-layout_util');
     wp_enqueue_script('viewer-layout_net');
     wp_enqueue_script('viewer-layout_init');

     wp_register_style('viewer_style', WP_PLUGIN_URL.'/nosuch/survey/css/spread-viewer/style.css');
     wp_enqueue_style('viewer_style');

     $res = <<<EOD
     <div class='layout-edit'>
          <div class='layout-messages'></div>
          <div class='layout-main'>
               <div class='layout-rows'>
                    <div class='layout-buff'>
                         <div class='screen'></div>
                    </div>
               </div>
          </div>
     </div>
     <div class='layout-controls'>
          <div class='layout-pages'></div>
          <div class='layout-tools'></div>
          <div class='layout-library'></div>
          <div class='layout-actions'></div>
     </div>

     <div class='offscreen'></div>
     <div class='printscreen'></div>
EOD;

     echo $res;
}
