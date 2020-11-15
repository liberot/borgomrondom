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

