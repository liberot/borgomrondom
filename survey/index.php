<?php defined('ABSPATH') || exit;

define ('BOOKBUILDER_VERSION', 'lenny.boockmackrr.0.1');
define ('SURVeY', sprintf('%s%s%s%s', DIRECTORY_SEPARATOR, 'bookbuilder', DIRECTORY_SEPARATOR, 'survey'));

require_once('include/config/config.php');

require_once('include/utils/log.php');
require_once('include/utils/db.php');
require_once('include/utils/utils.php');
require_once('include/utils/book.php');
require_once('include/utils/survey.php');
require_once('include/utils/thread.php');
require_once('include/utils/panel.php');
require_once('include/utils/upload.php');
require_once('include/utils/toc.php');
require_once('include/utils/typeform.php');
require_once('include/utils/layout.php');
require_once('include/utils/admin.php');
require_once('include/utils/utils.php');

require_once('include/services/book.php');
require_once('include/services/survey.php');
require_once('include/services/thread.php');
require_once('include/services/panel.php');
require_once('include/services/typeform.php');
require_once('include/services/upload.php');
require_once('include/services/toc.php');
require_once('include/services/layout.php');
require_once('include/services/print.php');
require_once('include/services/admin.php');

require_once('include/views/admin.php');
require_once('include/views/client.php');
require_once('include/views/web.php');

// add_action('init', function(){ wp_destroy_all_sessions(); exit(); });
// add_action('init', function(){ init_log('test', ['the1st'=>'xXx', 'the2nd'=>'yYY']); } );

