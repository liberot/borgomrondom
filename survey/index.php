<?php defined('ABSPATH') || exit;


define ('NOSUCH_VERSION', '003.yancsee');
define ('SURVeY', sprintf('%s%s%s%s', DIRECTORY_SEPARATOR, 'nosuch', DIRECTORY_SEPARATOR, 'survey'));

require_once('include/config/config.php');

require_once('include/utils/db.php');
require_once('include/utils/utils.php');
require_once('include/utils/book.php');
require_once('include/utils/survey.php');
require_once('include/utils/thread.php');
require_once('include/utils/board.php');
require_once('include/utils/upload.php');
require_once('include/utils/toc.php');
require_once('include/utils/typeform.php');
require_once('include/utils/layout.php');

require_once('include/services/book.php');
require_once('include/services/survey.php');
require_once('include/services/thread.php');
require_once('include/services/board.php');
require_once('include/services/typeform.php');
require_once('include/services/upload.php');
require_once('include/services/toc.php');
require_once('include/services/layout.php');
require_once('include/services/print.php');

require_once('include/views/admin.php');
require_once('include/views/client.php');
require_once('include/views/web.php');
require_once('include/utils/utils.php');

function on_plugin_activation(){
     // init_test_page();
     insert_guest_client();
     init_survey_page();
}

function on_plugin_deactivation(){
}

register_activation_hook(__FILE__, 'on_plugin_activation');
register_deactivation_hook(__FILE__, 'on_plugin_deactivation');

function set_dev_env(){
     // init_survey_page();
     // insert_guest_client();
     auth_guest_client(); 
}

function set_test_env(){
     require_once('survey/test/typeform.php');
     require_once('survey/test/survey.php');
     require_once('survey/test/session.php');
     require_once('survey/test/book.php');
     add_action('init', '__suspend__run__');
     function __suspend__run__(){
          exit();
     }
}

// add_action('init', function(){ wp_destroy_all_sessions(); exit(); });
add_action('init', function(){ set_dev_env(); });


