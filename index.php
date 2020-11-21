<?php

/**
 * Plugin Name: Book Builder
 * Plugin URI: http://127.0.0.1:8083
 * Description: Book Builder Plugin as for to build Print Spreads from generic Quesionnaire Resultsets
 * Version: 0.0.0.0.1
 * Author: Book Builder
 *
 * @package nosuch_survey
 */

defined('ABSPATH') || exit;

require_once('survey/index.php');

register_activation_hook(__FILE__, 'on_plugin_activation');
register_deactivation_hook(__FILE__, 'on_plugin_deactivation');

function set_dev_env(){
     add_action('init', 'init_survey_page');
     // add_action('init', 'insert_survey_client');
     // add_action('init', 'auth_survey_client');
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



