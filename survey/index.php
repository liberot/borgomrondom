<?php defined('ABSPATH') || exit;

define ('BOOKBUILDER_VERSION', 'ghissh.kahlattshoo.nagg.killlatshoo.s.0.1');
define ('SURVeY', sprintf('%s%s%s%s', DIRECTORY_SEPARATOR, 'bookbuilder', DIRECTORY_SEPARATOR, 'survey'));

/*
require_once('include1st/config/config.php');

require_once('include1st/utils/log.php');
require_once('include1st/utils/utils.php');
require_once('include1st/utils/book.php');
require_once('include1st/utils/survey.php');
require_once('include1st/utils/thread.php');
require_once('include1st/utils/section.php');
require_once('include1st/utils/panel.php');
require_once('include1st/utils/upload.php');
require_once('include1st/utils/toc.php');
require_once('include1st/utils/typeform.php');
require_once('include1st/utils/layout.php');
require_once('include1st/utils/admin.php');
require_once('include1st/utils/utils.php');
require_once('include1st/utils/typeform_crawler.php');

require_once('include1st/services/book.php');
require_once('include1st/services/survey.php');
require_once('include1st/services/thread.php');
require_once('include1st/services/section.php');
require_once('include1st/services/panel.php');
require_once('include1st/services/typeform.php');
require_once('include1st/services/upload.php');
require_once('include1st/services/toc.php');
require_once('include1st/services/layout.php');
require_once('include1st/services/print.php');
require_once('include1st/services/admin.php');

require_once('include1st/views/admin.php');
require_once('include1st/views/client.php');
require_once('include1st/views/web.php');

*/

// version 2
require_once('include2nd/config/config.php');
require_once('include2nd/utils/log.php');
require_once('include2nd/utils/db.php');
require_once('include2nd/utils/utils.php');
require_once('include2nd/utils/typeform.php');

require_once('include2nd/services/typeform.php');
require_once('include2nd/services/db.php');

require_once('include2nd/views/admin.php');

add_action('init', 'setup_env');
function setup_env(){
     // drop_tables();
     // init_tables();
     // init_log_utils();
     // insert_typeform_survey_from_descriptor('210902Cover--SgaKrUmI.json');
     // insert_typeform_surveys();
};
